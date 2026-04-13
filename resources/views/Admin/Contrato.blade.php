@extends('layouts.Ventas')
@section('Titulo', 'Contrato')

@section('css-vistaContrato')
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')

    <main class="main" id="contratoApp" data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
        data-numero="{{ $contrato->numero_contrato ?? '' }}" data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}"
        data-id-categoria="{{ $reservacion->id_categoria ?? '' }}">

        <header class="contrato-header-top">
            <div class="header-left">
                <h1 class="h1" style="margin-bottom:4px;">Gestión de Contrato</h1>
                <p style="color:#666; margin:0;">
                    <b>No. Contrato:</b> {{ $contrato->numero_contrato ?? '—' }}
                </p>
            </div>

            {{-- Resumen --}}
            <div class="header-right">
                <button type="button" class="resumen-barra-verde" id="btnToggleDetalle">
                    <div class="resumen-textos">
                        <span class="resumen-lbl">Total a pagar</span>
                        <div class="resumen-precio-mxn" id="resumenTotalBarra">
                            ${{ number_format($total ?? 0, 2) }} MXN
                        </div>
                        <div class="resumen-precio-usd" id="resumenTotalUsd">
                            ${{ number_format(($total ?? 0) / 18.5, 2) }} USD
                        </div>
                    </div>
                    <div class="resumen-iconos">
                        <span class="icono-flecha" id="iconoFlechaResumen">▼</span>
                        <span style="font-size: 24px;">🇲🇽</span>
                    </div>
                </button>

                <div class="resumen-detalle-desplegable" id="resumenDetalleContainer">

                    <div class="card resumen-card">
                        <div class="head">Resumen del Contrato</div>

                        <div class="cnt resumen-compacto" id="resumenCompacto">
                            <div id="vehiculo_info" class="vehiculo-mini-wrap">
                                <img id="resumenImgVeh" src="{{ $vehiculo->imagen_render ?? '' }}"
                                    alt="Imagen de {{ $vehiculo->marca ?? 'vehículo' }} {{ $vehiculo->modelo ?? '' }}"
                                    class="vehiculo-img">
                                <p class="vehiculo-nombre" id="resumenVehCompacto">—</p>
                                <p class="vehiculo-mini" id="resumenCategoriaCompacto">Categoría: —</p>
                                <p class="vehiculo-mini" id="resumenDiasCompacto">Días de renta: —</p>
                                <p class="vehiculo-mini" id="resumenFechasCompacto">— / —</p>
                            </div>
                            <div class="totalBox" style="margin-top:12px;">
                                <div class="kv">
                                    <div>Total actual</div>
                                    <div class="total" id="resumenTotalCompacto">${{ number_format($total ?? 0, 2) }} MXN
                                    </div>
                                </div>
                            </div>
                            <button id="btnVerDetalle" class="btn-resumen">Ver detalle ▼</button>
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
                                    <p><strong>Días totales:</strong> <span id="detDiasRenta">{{ $diasTotales }}</span>
                                    </p>
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
                                {{-- <section class="res-block">
                                    <h4>Servicios adicionales</h4>
                                    <ul id="r_cargos_lista" class="det-lista">
                                        <li class="empty">—</li>
                                    </ul>
                                    <p>Total: <b id="r_cargos_total">$0.00 MXN</b></p>
                                </section> --}}
                                <section class="res-block">
                                    <h4>Total desglosado</h4>
                                    <p>Tarifa base: <b id="r_base_precio">${{ number_format($precioReal, 2) }}</b>
                                        <button id="btnEditarTarifa"
                                            style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">✏️</button>
                                    </p>
                                    <p>Horas de cortesía: <b id="r_cortesia">{{ $reservacion->horas_cortesia ?? 1 }}</b>
                                        <button id="btnEditarCortesia"
                                            style="background:none; border:none; color:#2563eb; cursor:pointer; font-size:14px; margin-left:4px;">✏️</button>
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
                            <button id="btnOcultarDetalle" class="btn-resumen">Ocultar detalle ▲</button>
                        </div>
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
                    <div class="stepper-title">Documentación</div>
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
                        data-codigo="{{ $reservacion->codigo ?? '' }}"
                        data-inicio="{{ $reservacion->fecha_inicio ?? '' }}"
                        data-fin="{{ $reservacion->fecha_fin ?? '' }}">

                        <div class="grid-diseno-imagen">
                            <div class="col-reservacion">
                                <h3 class="titulo-seccion">Datos de Reservación</h3>

                                <div class="campo-resumen">
                                    <label class="campo-resumen-label">Código Reservación</label>
                                    <span class="campo-resumen-valor">{{ strtoupper($reservacion->codigo ?? '—') }}</span>
                                </div>

                                <div class="campo-resumen" style="margin-bottom: 30px;">
                                    <label class="campo-resumen-label">Titular</label>
                                    <span class="campo-resumen-valor">{{ strtoupper($reservacion->nombre_cliente ?? '') }}
                                        {{ strtoupper($reservacion->apellidos_cliente ?? '') }}</span>
                                </div>

                                <div class="flex-fechas">
                                    {{-- Tarjeta Entrega --}}
                                    <div class="tarjeta-fecha">
                                        <div class="tarjeta-fecha-header">ENTREGA</div>
                                        <div class="tarjeta-fecha-body">
                                            <span id="txtDiaEntrega"
                                                class="fecha-numero">{{ $fechaInicio->format('d') }}</span>
                                            <div class="fecha-mes-anio">
                                                <span
                                                    id="txtMesEntrega">{{ strtoupper($fechaInicio->translatedFormat('M')) }}</span>
                                                <span id="txtAnioEntrega">{{ $fechaInicio->format('Y') }}</span>
                                            </div>
                                            <div id="txtHoraEntrega" class="fecha-hora">{{ $horaRetiro->format('H:i') }}
                                                HRS</div>
                                        </div>
                                        <input type="datetime-local" id="inputOcultoEntrega"
                                            value="{{ $fechaInicio->format('Y-m-d\TH:i') }}" class="input-oculto-fecha">
                                    </div>

                                    {{-- Tarjeta Devolución --}}
                                    <div class="tarjeta-fecha">
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
                                        <input type="datetime-local" id="inputOcultoDevolucion"
                                            value="{{ $fechaFin->format('Y-m-d\TH:i') }}" class="input-oculto-fecha">
                                    </div>
                                </div>
                            </div>

                            <div class="col-auto">
                                <h3 class="titulo-seccion">Datos del Auto</h3>

                                <h2 id="detNombreVehiculoStep1" class="vehiculo-nombre-paso1">
                                    {{ $vehiculo->nombre_publico ?? trim(($vehiculo->marca ?? '') . ' ' . ($vehiculo->modelo ?? '')) ?: 'Vehículo' }}
                                </h2>

                                <p class="vehiculo-categoria-paso1">
                                    <span id="detCategoriaCodigoStep1">{{ $codigoCat }}</span> |
                                    <span id="detCategoriaNombreStep1">{{ strtoupper($catActual->nombre ?? '') }}</span>
                                </p>

                                <div class="imagen-vehiculo-container">
                                    <img src="{{ $imgFinal }}" id="mainImgVeh" class="imagen-vehiculo"
                                        alt="Coche">
                                </div>

                                <div class="especificaciones-grid">
                                    <div class="especificacion-item">
                                        <span class="especificacion-label">Puertas</span>
                                        <span id="step1Puertas"
                                            class="especificacion-valor">{{ $vehiculo->puertas ?? 0 }}</span>
                                    </div>
                                    <div class="especificacion-item">
                                        <span class="especificacion-label">Pasajeros</span>
                                        <span id="step1Pasajeros"
                                            class="especificacion-valor">{{ $vehiculo->asientos ?? 0 }}</span>
                                    </div>
                                    <div class="especificacion-item">
                                        <span class="especificacion-label">Transmisión</span>
                                        <span id="step1Transmision"
                                            class="especificacion-valor">{{ strtoupper($vehiculo->transmision ?? 'N/A') }}</span>
                                    </div>
                                </div>

                                <div class="botones-accion-paso1">
                                    <button type="button" class="btn secondary" id="btnElegirVehiculo">Cambiar
                                        Vehículo</button>
                                    <button class="btn primary" id="go2">Continuar</button>
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
                                <div id="serviciosGrid" class="add-grid">
                                    {{-- TARJETAS DE SERVICIOS NORMALES --}}
                                    @forelse ($servicios as $s)
                                        <div class="card-servicio" data-id="{{ $s->id_servicio }}"
                                            data-precio="{{ $s->precio }}" data-tipo="{{ $s->tipo_cobro }}"
                                            data-nombre="{{ $s->nombre }}">

                                            <div class="servicio-header">{{ $s->nombre }}</div>

                                            @if ($s->descripcion)
                                                <p class="servicio-descripcion">{{ $s->descripcion }}</p>
                                            @endif

                                            <div class="servicio-precio">
                                                <strong>${{ number_format($s->precio, 2) }} MXN/día</strong>
                                            </div>

                                            <div class="contador">
                                                <button class="btn-contador menos">−</button>
                                                <span
                                                    class="cantidad">{{ $serviciosReservados[$s->id_servicio] ?? 0 }}</span>
                                                <button class="btn-contador mas">+</button>
                                            </div>
                                        </div>
                                    @empty
                                        <p>No hay servicios adicionales disponibles.</p>
                                    @endforelse

                                    {{-- SERVICIO DE DELIVERY --}}
                                    <div class="cargo-item delivery-wrapper {{ !empty($delivery->activo) ? 'active' : '' }}"
                                        data-id-reservacion="{{ $reservacion->id_reservacion }}"
                                        data-delivery-activo="{{ $delivery->activo ?? 0 }}"
                                        data-delivery-km="{{ $delivery->kms ?? '' }}"
                                        data-delivery-direccion="{{ $delivery->direccion ?? '' }}"
                                        data-delivery-total="{{ $delivery->total ?? 0 }}"
                                        data-delivery-ubicacion="{{ isset($delivery->id_ubicacion) ? $delivery->id_ubicacion : '' }}"
                                        data-costo-km="{{ $costoKmCategoria ?? 0 }}">

                                        <div class="cargo-item-header">Servicio de Delivery</div>
                                        <div class="cargo-item-icon">🚚</div>
                                        <p class="cargo-item-desc">Entrega del vehículo a domicilio.</p>

                                        <div class="cargo-item-toggle">
                                            <label class="switch switch-toggle">
                                                <input type="checkbox" id="deliveryToggle" name="delivery_activo"
                                                    {{ !empty($delivery->activo) ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                            <span>Activar</span>
                                        </div>

                                        <div id="deliveryFields" class="cargo-item-fields"
                                            style="display: {{ !empty($delivery->activo) ? 'block' : 'none' }};">
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
                                                <input type="text" id="deliveryDireccion" class="form-control-simple"
                                                    placeholder="Ej. Centro" value="{{ $delivery->direccion ?? '' }}">
                                            </div>
                                            <div id="groupKm" class="form-group"
                                                style="display: {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'block' : 'none' }};">
                                                <label>Distancia (Km)</label>
                                                <input type="number" min="0" id="deliveryKm"
                                                    class="form-control-simple" placeholder="0"
                                                    value="{{ $delivery->kms ?? '' }}">
                                            </div>
                                            <div class="cargo-item-total">
                                                <div class="cargo-item-total-label">Costo Delivery</div>
                                                <div id="deliveryTotal" class="cargo-item-total-amount">
                                                    ${{ number_format($delivery->total ?? 0, 2) }} MXN
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="deliveryPrecioKm"
                                            value="{{ $costoKmCategoria ?? 0 }}">
                                        <input type="hidden" id="deliveryTotalHidden"
                                            value="{{ $delivery->total ?? 0 }}">
                                    </div>

                                    {{-- SERVICIO DE DROPOFF --}}
                                    <div class="cargo-item dropoff-wrapper {{ $dropActivo ?? false ? 'active' : '' }}"
                                        data-id="6" data-monto="{{ $dropTotal ?? 0 }}">
                                        <div class="cargo-item-header">Servicio de Dropoff</div>
                                        <div class="cargo-item-icon">📍</div>
                                        <p class="cargo-item-desc">Recolección del vehículo en otro destino.</p>

                                        <div class="cargo-item-toggle">
                                            <label class="switch switch-toggle">
                                                <input type="checkbox" id="switchDropoffCheckbox"
                                                    {{ $dropActivo ?? false ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                            <span>Activar</span>
                                        </div>

                                        <div id="dropoffFields" class="cargo-item-fields"
                                            style="display: {{ $dropActivo ?? false ? 'block' : 'none' }};">
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
                                                    <option value="0" {{ $esManual ?? false ? 'selected' : '' }}>
                                                        Dirección manual</option>
                                                </select>
                                            </div>
                                            <div id="dropGroupDireccion" class="form-group"
                                                style="display: {{ $esManual ?? false ? 'block' : 'none' }};">
                                                <label>Dirección</label>
                                                <input type="text" id="dropDireccion" class="form-control-simple"
                                                    placeholder="Ej. Calle Las Flores" value="{{ $dropDest ?? '' }}">
                                            </div>
                                            <div id="dropGroupKm" class="form-group"
                                                style="display: {{ $esManual ?? false ? 'block' : 'none' }};">
                                                <label>Distancia (Km)</label>
                                                <input type="number" min="0" id="dropKm"
                                                    class="form-control-simple" placeholder="Ej. 25"
                                                    value="{{ $dropKm ?? '' }}">
                                            </div>
                                            <div id="dropCostoKm" class="cargo-item-costo-km">
                                                Costo por km: <b
                                                    id="dropCostoKmHTML">${{ number_format($costoKmCategoria ?? 0, 2) }}</b>
                                            </div>
                                            <div class="cargo-item-total">
                                                <div class="cargo-item-total-label">Total Dropoff</div>
                                                <div id="dropTotalHTML" class="cargo-item-total-amount">
                                                    ${{ number_format($dropTotal ?? 0, 2) }} MXN
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- GASOLINA PREPAGO --}}
                                    <div class="cargo-item gasolina-wrapper {{ $cargoGas ? 'active' : '' }}"
                                        data-id="5" data-monto="{{ $cargoGas->monto ?? 0 }}">
                                        <div class="cargo-item-header">Gasolina Prepago</div>
                                        <div class="cargo-item-icon">⛽</div>
                                        <p class="cargo-item-desc">Paga solo la gasolina faltante para devolverlo vacío.
                                            ($20.00 MXN / Litro)</p>

                                        <div class="cargo-item-toggle">
                                            <label class="switch switch-toggle">
                                                <input type="checkbox" id="switchGasolinaCheckbox"
                                                    {{ $cargoGas ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                            <span>Activar</span>
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
                                                <div class="cargo-item-total-label">Costo Gasolina</div>
                                                <div id="gasTotalHTML" class="cargo-item-total-amount">
                                                    ${{ number_format($cargoGas->monto ?? 0, 2) }} MXN
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="gasNivelActual" value="{{ $nivelFraccion }}">
                                        <input type="hidden" id="gasPrecioLitro" value="20">
                                    </div>
                                </div>

                                <div class="acciones">
                                    <button class="btn gray" id="back1">← Atrás</button>
                                    <button class="btn primary" id="go3">Continuar protecciones →</button>
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

                            {{-- VISTA DE PAQUETES (MOSTRADA POR DEFECTO) --}}
                            <div id="vista-paquetes">
                                <div class="paquetes-grid">
                                    @foreach ($seguros as $seguro)
                                        <div class="paquete-card card is-paquete seguro-item"
                                            data-id="{{ $seguro->id_seguro }}"
                                            data-precio="{{ $seguro->precio_por_dia }}">
                                            <div class="paquete-header">{{ $seguro->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">✔</div>
                                                <div class="cobertura-lista">
                                                    {!! nl2br(e($seguro->cobertura)) !!}
                                                </div>
                                                <div class="precio">
                                                    $ {{ number_format($seguro->precio_por_dia, 2) }} MXN x Día
                                                </div>
                                                <div class="seleccion-toggle">
                                                    <input type="radio" id="pack_{{ $seguro->id_seguro }}"
                                                        name="paquete_seguro" value="{{ $seguro->id_seguro }}"
                                                        class="input-paquete"
                                                        {{ $seguro->id_seguro == ($seguroSeleccionado->id_seguro ?? null) ? 'checked' : '' }}>
                                                    <label for="pack_{{ $seguro->id_seguro }}">Seleccionar
                                                        Paquete</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- VISTA DE INDIVIDUALES (OCULTA POR DEFECTO) --}}
                            <div id="vista-individuales" class="vista-individuales" style="display: none;">
                                <div class="nota-amarilla">
                                    <strong>Modo Personalizado:</strong> Arma tu propio paquete seleccionando protecciones
                                    individuales.
                                </div>

                                {{-- 1. Colisión y robo --}}
                                <h4 class="categoria-titulo-individual">Colisión y robo</h4>
                                <div class="paquetes-grid">
                                    @foreach ($grupo_colision as $ind)
                                        <div class="paquete-card card is-individual individual-item"
                                            data-id="{{ $ind->id_individual }}"
                                            data-precio="{{ $ind->precio_por_dia }}">
                                            <div class="paquete-header">{{ $ind->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">➕</div>
                                                <div class="cobertura-lista">{{ $ind->descripcion }}</div>
                                                <div class="precio">$ {{ number_format($ind->precio_por_dia, 2) }} MXN x
                                                    Día</div>
                                                <div class="seleccion-toggle">
                                                    <input type="checkbox" id="ind_{{ $ind->id_individual }}"
                                                        value="{{ $ind->id_individual }}"
                                                        class="input-individual switch-individual"
                                                        data-id="{{ $ind->id_individual }}">
                                                    <label for="ind_{{ $ind->id_individual }}">Incluir</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- 2. Gastos médicos --}}
                                <h4 class="categoria-titulo-individual">Gastos médicos</h4>
                                <div class="paquetes-grid">
                                    @foreach ($grupo_medicos as $ind)
                                        <div class="paquete-card card is-individual individual-item"
                                            data-id="{{ $ind->id_individual }}"
                                            data-precio="{{ $ind->precio_por_dia }}">
                                            <div class="paquete-header">{{ $ind->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">➕</div>
                                                <div class="cobertura-lista">{{ $ind->descripcion }}</div>
                                                <div class="precio">$ {{ number_format($ind->precio_por_dia, 2) }} MXN x
                                                    Día</div>
                                                <div class="seleccion-toggle">
                                                    <input type="checkbox" id="ind_{{ $ind->id_individual }}"
                                                        value="{{ $ind->id_individual }}"
                                                        class="input-individual switch-individual"
                                                        data-id="{{ $ind->id_individual }}">
                                                    <label for="ind_{{ $ind->id_individual }}">Incluir</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- 3. Asistencia para el camino --}}
                                <h4 class="categoria-titulo-individual">Asistencia para el camino</h4>
                                <div class="paquetes-grid">
                                    @foreach ($grupo_asistencia as $ind)
                                        <div class="paquete-card card is-individual individual-item"
                                            data-id="{{ $ind->id_individual }}"
                                            data-precio="{{ $ind->precio_por_dia }}">
                                            <div class="paquete-header">{{ $ind->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">➕</div>
                                                <div class="cobertura-lista">{{ $ind->descripcion }}</div>
                                                <div class="precio">$ {{ number_format($ind->precio_por_dia, 2) }} MXN x
                                                    Día</div>
                                                <div class="seleccion-toggle">
                                                    <input type="checkbox" id="ind_{{ $ind->id_individual }}"
                                                        value="{{ $ind->id_individual }}"
                                                        class="input-individual switch-individual"
                                                        data-id="{{ $ind->id_individual }}">
                                                    <label for="ind_{{ $ind->id_individual }}">Incluir</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- 4. Daños a terceros --}}
                                <h4 class="categoria-titulo-individual">Daños a terceros</h4>
                                <div class="paquetes-grid">
                                    @foreach ($grupo_terceros as $ind)
                                        <div class="paquete-card card is-individual individual-item"
                                            data-id="{{ $ind->id_individual }}"
                                            data-precio="{{ $ind->precio_por_dia }}">
                                            <div class="paquete-header">{{ $ind->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">➕</div>
                                                <div class="cobertura-lista">{{ $ind->descripcion }}</div>
                                                <div class="precio">$ {{ number_format($ind->precio_por_dia, 2) }} MXN x
                                                    Día</div>
                                                <div class="seleccion-toggle">
                                                    <input type="checkbox" id="ind_{{ $ind->id_individual }}"
                                                        value="{{ $ind->id_individual }}"
                                                        class="input-individual switch-individual"
                                                        data-id="{{ $ind->id_individual }}">
                                                    <label for="ind_{{ $ind->id_individual }}">Incluir</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- 5. Protecciones automáticas --}}
                                <h4 class="categoria-titulo-individual">Protecciones automáticas</h4>
                                <div class="paquetes-grid">
                                    @foreach ($grupo_protecciones as $ind)
                                        <div class="paquete-card card is-individual individual-item"
                                            data-id="{{ $ind->id_individual }}"
                                            data-precio="{{ $ind->precio_por_dia }}">
                                            <div class="paquete-header">{{ $ind->nombre }}</div>
                                            <div class="paquete-body">
                                                <div class="icon-check">➕</div>
                                                <div class="cobertura-lista">{{ $ind->descripcion }}</div>
                                                <div class="precio">$ {{ number_format($ind->precio_por_dia, 2) }} MXN x
                                                    Día</div>
                                                <div class="seleccion-toggle">
                                                    <input type="checkbox" id="ind_{{ $ind->id_individual }}"
                                                        value="{{ $ind->id_individual }}"
                                                        class="input-individual switch-individual"
                                                        data-id="{{ $ind->id_individual }}">
                                                    <label for="ind_{{ $ind->id_individual }}">Incluir</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- SECCIÓN DE TOTAL Y BOTONES --}}
                            <div class="resumen-total-inline">
                                Total de protecciones: <span id="total_seguros"
                                    class="total-seguros-color">${{ isset($seguroSeleccionado) ? number_format($seguroSeleccionado->precio_por_dia, 2) : '0.00' }}
                                    MXN</span>
                            </div>

                            <div class="botones-accion-paso3">
                                <button type="button" class="btn-gris-ancho" id="btnToggleVista">Seleccion Individual
                                    Protecciones</button>
                                <button type="button" class="btn-rojo-ancho disabled" id="go4">Continuar con
                                    Adicionales</button>
                            </div>

                            <div class="boton-atras-paso3">
                                <button class="btn gray" id="back2"> ← Atrás</button>
                            </div>

                        </div>
                    </div>
                </article>

            </section>

        </div>

        {{-- Modales --}}

        {{-- Modal de vehiculos --}}
        <div id="modalVehiculos" class="modal-vehiculos">
            <div class="modal-content modal-vehiculos-content">
                <div class="modal-header modal-vehiculos-header">
                    <span class="modal-vehiculos-titulo">Inventario de Vehículos</span>
                    <button type="button" id="cerrarModalVehiculos" class="close-btn modal-close-btn">✕</button>
                </div>

                {{-- Filtros superiores --}}
                <div class="modal-filtros modal-vehiculos-filtros">
                    <span class="filtros-label">Filtros rápidos:</span>
                    <input type="text" id="filtroPlacas" placeholder="Placas..." class="filtro-input">
                    <input type="text" id="filtroColor" placeholder="Color..." class="filtro-input">
                    <input type="text" id="filtroModelo" placeholder="Modelo..." class="filtro-input">
                </div>

                {{-- Tabla con scroll --}}
                <div class="table-responsive modal-vehiculos-tabla">
                    <table class="tabla-excel-vehiculos">
                        <thead>
                            <tr>
                                <th>MVA</th>
                                <th>PLACAS</th>
                                <th>CATEGORIA</th>
                                <th>TAMAÑO</th>
                                <th>MODELO</th>
                                <th>Trasmisión</th>
                                <th>COLOR</th>
                                <th>Gasolina</th>
                                <th>GAS EN LITROS</th>
                                <th>KILOMETRAJE</th>
                                <th>VERIFICACION</th>
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

                {{-- Footer con botón cerrar --}}
                <div class="modal-footer modal-vehiculos-footer">
                    <div class="footer-boton-container">
                        <button id="cerrarModalVehiculos2" class="btn gray modal-cerrar-btn">Cerrar inventario</button>
                    </div>
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
