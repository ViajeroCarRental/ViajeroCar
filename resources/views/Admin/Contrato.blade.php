@extends('layouts.Ventas')
@section('Titulo', 'Contrato')

@section('css-vistaContrato')
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')

    @php
        use Carbon\Carbon;

        // Fechas y Días
        $fechaInicio = Carbon::parse($reservacion->fecha_inicio ?? now());
        $fechaFin = Carbon::parse($reservacion->fecha_fin ?? now()->addDay());
        $horaRetiro = Carbon::parse($reservacion->hora_retiro ?? '12:00:00');
        $horaEntrega = Carbon::parse($reservacion->hora_entrega ?? '12:00:00');

        $diasTotales = $fechaInicio->diffInDays($fechaFin);
        if ($diasTotales < 1) {
            $diasTotales = 1;
        }

        // Precio
        $catActual = $categorias->where('id_categoria', $reservacion->id_categoria ?? 0)->first();
        $precioBase = $catActual->precio_dia ?? ($catActual->precio ?? 0);

        $categoriasCol = collect($categorias ?? []);
        $catActual = $categoriasCol->where('id_categoria', $reservacion->id_categoria ?? 0)->first();

        $esAjustada = $reservacion->tarifa_ajustada ?? 0;
        $tarifaModificada = $reservacion->tarifa_modificada ?? 0;

        $precioReal = $esAjustada == 1 && $tarifaModificada > 0 ? $tarifaModificada : $precioBase;

        // Cálculos Financieros
        $subtotal = $diasTotales * $precioReal;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        // Formato de Teléfono
        $telOriginal = $reservacion->telefono_cliente ?? '';
        $soloNumeros = preg_replace('/[^0-9]/', '', $telOriginal);

        if (strlen($soloNumeros) == 12) {
            $telFinal =
                '+52 (' .
                substr($soloNumeros, 2, 3) .
                ') ' .
                substr($soloNumeros, 5, 3) .
                '-' .
                substr($soloNumeros, 8);
        } elseif (strlen($soloNumeros) == 10) {
            $telFinal =
                '(' . substr($soloNumeros, 0, 3) . ') ' . substr($soloNumeros, 3, 3) . '-' . substr($soloNumeros, 6);
        } else {
            $telFinal = $telOriginal ?: '—';
        }
    @endphp

    <main class="main" id="contratoApp" data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
        data-numero="{{ $contrato->numero_contrato ?? '' }}" data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}">

        <h1 class="h1">Gestión de Contrato</h1>
        <p style="color:#666; margin-bottom:10px;">
            <b>No. Contrato:</b> {{ $contrato->numero_contrato ?? '—' }}
        </p>

        <div class="grid">

            <section class="steps">

                {{-- Paso 1 --}}
                <article class="step active" data-step="1">
                    <header>
                        <div class="badge">1</div>
                        <h3>PASO 1 · Datos de la Reservación</h3>
                    </header>

                    <div class="body contrato-resumen" id="contratoInicial"
                        data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
                        data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}"
                        data-codigo="{{ $reservacion->codigo ?? '' }}"
                        data-nombre="{{ $reservacion->nombre_cliente ?? '' }}"
                        data-email="{{ $reservacion->email_cliente ?? '' }}"
                        data-telefono="{{ $reservacion->telefono_cliente ?? '' }}"
                        data-inicio="{{ $reservacion->fecha_inicio ?? '' }}"
                        data-fin="{{ $reservacion->fecha_fin ?? '' }}"
                        data-hora-retiro="{{ $reservacion->hora_retiro ?? '' }}"
                        data-hora-entrega="{{ $reservacion->hora_entrega ?? '' }}"
                        data-total="{{ $reservacion->total ?? '' }}">

                        <div class="card resumen-header">
                            <div class="row">
                                <div>
                                    <h4>Código de reservación</h4>
                                    <p id="codigo">{{ strtoupper($reservacion->codigo) }}</p>
                                </div>
                                <div>
                                    <h4>Titular de la reservación</h4>
                                    <p id="clienteNombre">{{ strtoupper($reservacion->nombre_cliente ?? '—') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="resumen-entrega">
                            <div class="bloque entrega">
                                <div class="titulo">ENTREGA</div>
                                <p class="lugar">Sucursal de origen</p>
                                <div class="fecha fecha-entrega-display">
                                    <div class="dia">{{ $fechaInicio->format('d') }}</div>
                                    <div class="mes">{{ strtoupper($fechaInicio->format('M')) }}</div>
                                    <div class="anio">{{ $fechaInicio->format('Y') }}</div>
                                    <span class="edit-icon fecha-entrega-edit" title="Solicitar cambio">✏️</span>
                                </div>
                                <div class="fecha-edicion-entrega" style="display:none; margin-top:8px;">
                                    <input type="date" id="nuevaFechaEntrega" disabled>
                                    <input type="time" id="nuevaHoraEntrega" disabled>
                                    <button type="button" class="btn small" id="btnSolicitarCambioEntrega">Solicitar
                                        autorización</button>
                                </div>
                                <div class="hora">{{ $horaRetiro->format('h:i A') }}</div>
                            </div>

                            <div class="bloque devolucion">
                                <div class="titulo">DEVOLUCIÓN</div>
                                <p class="lugar">Sucursal destino</p>
                                <div class="fecha fecha-devolucion-display">
                                    <div class="dia">{{ $fechaFin->format('d') }}</div>
                                    <div class="mes">{{ strtoupper($fechaFin->format('M')) }}</div>
                                    <div class="anio">{{ $fechaFin->format('Y') }}</div>
                                    <span class="edit-icon fecha-devolucion-edit"
                                        title="Editar fecha de devolución">✏️</span>
                                </div>
                                <div class="fecha-edicion-devolucion" style="display:none; margin-top:8px;">
                                    <input type="date" id="nuevaFechaDevolucion">
                                    <input type="time" id="nuevaHoraDevolucion">
                                    <button type="button" class="btn small" id="btnGuardarFechaDevolucion">Guardar</button>
                                </div>
                                <div class="hora">{{ $horaEntrega->format('h:i A') }}</div>
                            </div>
                        </div>

                        <div class="card resumen-totales">
                            <div class="kv">
                                <div>Teléfono</div>
                                <div id="clienteTel">
                                    {{ $telFinal }}
                                </div>
                            </div>
                            <div class="kv">
                                <div>Correo electrónico</div>
                                <div id="clienteEmail">{{ $reservacion->email_cliente }}</div>
                            </div>
                            <div class="kv">
                                <div>Duración</div>
                                <div id="diasBadge">{{ $diasTotales }} días</div>
                            </div>
                            <div class="kv total">
                                <div style="font-weight:bold;color:#d00;">Total reservado</div>
                                <div class="total" id="totalReserva" style="font-weight:bold;color:#d00;">
                                    ${{ number_format($reservacion->total, 2) }} MXN
                                </div>
                            </div>
                        </div>

                        <div class="card" style="margin-top:20px;">
                            <label style="font-weight:bold;">Categoría reservada</label>
                            <select id="selectCategoria" class="input" style="width:100%; margin-top:8px;">
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id_categoria }}"
                                        {{ $reservacion->id_categoria == $cat->id_categoria ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-top:15px; text-align:left;">
                            <button type="button" class="btn secondary" id="btnElegirVehiculo">🚗 Elegir vehículo</button>
                        </div>

                        <div class="acciones" style="margin-top:20px;text-align:right;">
                            <button class="btn primary" id="go2" type="button">Continuar</button>
                        </div>

                    </div>
                </article>

                {{-- Paso 2 --}}
                <article class="step" data-step="2">
                    <header>
                        <div class="badge">2</div>
                        <h3>PASO 2 · Servicios adicionales</h3>
                    </header>

                    <div class="body">
                        <section class="section">
                            <div class="head">Selecciona servicios adicionales</div>

                            <div class="cnt">
                                <div id="serviciosGrid" class="add-grid">
                                    @forelse ($servicios as $s)
                                        @php
                                            $cantidad = $serviciosReservados[$s->id_servicio] ?? 0;
                                        @endphp
                                        <div class="card-servicio" data-id="{{ $s->id_servicio }}"
                                            data-precio="{{ $s->precio }}" data-tipo="{{ $s->tipo_cobro }}"
                                            data-nombre="{{ $s->nombre }}">
                                            <h4>{{ $s->nombre }}</h4>
                                            @if ($s->descripcion)
                                                <p>{{ $s->descripcion }}</p>
                                            @endif
                                            <div class="precio">
                                                <strong>${{ number_format($s->precio, 2) }} MXN/día</strong>
                                            </div>
                                            <div class="contador">
                                                <button class="menos">−</button>
                                                <span class="cantidad">{{ $cantidad }}</span>
                                                <button class="mas">+</button>
                                            </div>
                                        </div>
                                    @empty
                                        <p>No hay servicios adicionales disponibles.</p>
                                    @endforelse
                                </div>

                                <div class="delivery-wrapper" style="margin-top:25px;"
                                    data-id-reservacion="{{ $reservacion->id_reservacion }}"
                                    data-delivery-activo="{{ $delivery->activo ?? 0 }}"
                                    data-delivery-km="{{ $delivery->kms ?? '' }}"
                                    data-delivery-direccion="{{ $delivery->direccion ?? '' }}"
                                    data-delivery-total="{{ $delivery->total ?? 0 }}"
                                    data-delivery-ubicacion="{{ isset($delivery->id_ubicacion) ? $delivery->id_ubicacion : '' }}"
                                    data-costo-km="{{ $costoKmCategoria }}">

                                    <div class="head" style="margin-bottom:10px;">Delivery</div>

                                    <label class="switch">
                                        <input type="checkbox" id="deliveryToggle" name="delivery_activo"
                                            {{ !empty($delivery->activo) ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>

                                    <div id="deliveryFields"
                                        style="display: {{ !empty($delivery->activo) ? 'block' : 'none' }}; margin-top:20px;">
                                        <div class="form-group">
                                            <label>Seleccionar ubicación</label>
                                            <select id="deliveryUbicacion" name="delivery_ubicacion"
                                                class="form-control">
                                                <option value="">Seleccione...</option>
                                                @foreach ($ubicaciones as $u)
                                                    <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km }}"
                                                        {{ !empty($delivery->id_ubicacion) && $delivery->id_ubicacion == $u->id_ubicacion ? 'selected' : '' }}>
                                                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km }}
                                                        km)
                                                    </option>
                                                @endforeach
                                                <option value="0"
                                                    {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'selected' : '' }}>
                                                    Dirección personalizada (manual)
                                                </option>
                                            </select>
                                        </div>

                                        <div id="groupDireccion" class="form-group"
                                            style="margin-top:15px; display: {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'block' : 'none' }};">
                                            <label>Dirección personalizada (opcional)</label>
                                            <input type="text" id="deliveryDireccion" name="delivery_direccion"
                                                class="form-control" placeholder="Ej. Calle Robles 123, Centro"
                                                value="{{ $delivery->direccion ?? '' }}">
                                        </div>

                                        <div id="groupKm" class="form-group"
                                            style="margin-top:15px; display: {{ isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0 ? 'block' : 'none' }};">
                                            <label>Kilómetros personalizados</label>
                                            <input type="number" min="0" id="deliveryKm" name="delivery_km"
                                                class="form-control" placeholder="Ej. 15"
                                                value="{{ $delivery->kms ?? '' }}">
                                        </div>

                                        <div style="margin-top:15px; font-weight:bold;">
                                            Total Delivery:
                                            <span id="deliveryTotal">
                                                ${{ number_format($delivery->total ?? 0, 2) }} MXN
                                            </span>
                                        </div>
                                    </div>

                                    <input type="hidden" id="deliveryPrecioKm" value="{{ $costoKmCategoria }}">
                                    <input type="hidden" id="deliveryTotalHidden" value="{{ $delivery->total ?? 0 }}">
                                </div>

                                <div class="totalBox" style="margin-top:20px;">
                                    <div class="kv">
                                        <div>Total adicionales</div>
                                        <div class="total" id="total_servicios">$0.00 MXN</div>
                                    </div>
                                </div>

                                <div class="acciones" style="margin-top:20px;">
                                    <button class="btn gray" id="back1" type="button">← Atrás</button>
                                    <button class="btn primary" id="go3" type="button">Continuar →</button>
                                </div>

                            </div>
                        </section>
                    </div>
                </article>

                {{-- Paso 3 --}}
                <article class="step" data-step="3">
                    <header>
                        <div class="badge">3</div>
                        <h3>PASO 3 · Protecciones del contrato</h3>
                    </header>

                    <div class="body">
                        <section class="section">
                            <div class="head">Selecciona un paquete o protecciones individuales</div>

                            <div class="cnt">
                                <div class="note">
                                    Si eliges un <b>paquete</b>, se desactivan las individuales.
                                    Si activas alguna <b>individual</b>, se desmarca el paquete.
                                </div>

                                <div style="display:flex; gap:12px; margin:15px 0;">
                                    <button type="button" class="btn primary" id="btnVerPaquetes">Ver paquetes de
                                        seguro</button>
                                    <button type="button" class="btn gray" id="btnVerIndividuales">Armar mi
                                        paquete</button>
                                </div>

                                <div class="totalBox" style="margin-top:18px;">
                                    <div class="kv">
                                        <div>Total protecciones</div>
                                        <div class="total" id="total_seguros">
                                            ${{ isset($seguroSeleccionado) ? number_format($seguroSeleccionado->precio_por_dia, 2) : '0.00' }}
                                            MXN
                                        </div>
                                    </div>
                                </div>

                                <div class="acciones" style="margin-top:20px; display:flex; gap:10px;">
                                    <button class="btn gray" id="back2" type="button">← Atrás</button>
                                    <button type="button" class="btn primary" id="go4"> Continuar → </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </article>

            </section>

            {{-- Resumen --}}
            <aside class="sticky">
                <div class="card resumen-card">
                    <div class="head">Resumen del Contrato</div>

                    <div class="cnt resumen-compacto" id="resumenCompacto">
                        <div id="vehiculo_info" class="vehiculo-mini-wrap">
                            <img id="resumenImgVeh" src="{{ asset('img/default-car.png') }}" alt="Vehículo"
                                class="vehiculo-img">
                            <p class="vehiculo-nombre" id="resumenVehCompacto">—</p>
                            <p class="vehiculo-mini" id="resumenCategoriaCompacto">Categoría: —</p>
                            <p class="vehiculo-mini" id="resumenDiasCompacto">Días de renta: —</p>
                            <p class="vehiculo-mini" id="resumenFechasCompacto">— / —</p>
                        </div>
                        <div class="totalBox" style="margin-top:12px;">
                            <div class="kv">
                                <div>Total actual</div>
                                <div class="total" id="resumenTotalCompacto">$0.00 MXN</div>
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
                                <p id="detCliente">
                                    {{ strtoupper($reservacion->nombre_cliente ?? '—') }}
                                </p>

                                <p id="detTelefono">
                                    {{ $telFinal }}
                                </p>

                                <p id="detEmail">
                                    {{ $reservacion->email_cliente ?? '—' }}
                                </p>
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

                                <p>Salida:
                                    <span id="detFechaSalida">{{ $fechaInicio->format('Y-m-d') }}</span> ·
                                    <span id="detHoraSalida">{{ $horaRetiro->format('h:i A') }}</span>
                                </p>

                                <p>Entrega:
                                    <span id="detFechaEntrega">{{ $fechaFin->format('Y-m-d') }}</span> ·
                                    <span id="detHoraEntrega">{{ $horaEntrega->format('h:i A') }}</span>
                                </p>

                                <p>
                                    <strong>Días totales:</strong>
                                    <span id="detDiasRenta">{{ $diasTotales }}</span>
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
                            <section class="res-block">
                                <h4>Servicios adicionales</h4>
                                <ul id="r_cargos_lista" class="det-lista">
                                    <li class="empty">—</li>
                                </ul>
                                <p>Total: <b id="r_cargos_total">$0.00 MXN</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Total desglosado</h4>

                                <p>Tarifa base:
                                    <b id="r_base_precio">${{ number_format($precioReal, 2) }}</b>
                                    <button id="btnEditarTarifa"
                                        style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">✏️</button>
                                </p>

                                <p>Horas de cortesía:
                                    <b id="r_cortesia">{{ $reservacion->horas_cortesia ?? 1 }}</b>
                                    <button id="btnEditarCortesia"
                                        style="background:none; border:none; color:#2563eb; cursor:pointer; font-size:14px; margin-left:4px;"
                                        title="Editar cortesía">
                                        ✏️
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
                        <button id="btnOcultarDetalle" class="btn-resumen">Ocultar detalle ▲</button>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Modal de vehiculos --}}
        <div id="modalVehiculos" class="modal-vehiculos">
            <div class="modal-content">
                <div class="modal-header">
                    <span>Vehículos disponibles</span>
                    <button type="button" id="cerrarModalVehiculos" class="close-btn">✕</button>
                </div>
                <div class="modal-select-categoria" style="margin: 15px 0;">
                    <label style="font-weight:600; font-size:14px;">Filtrar por categoría</label>
                    <select id="selectCategoriaModal" class="filtro-input" style="margin-top:6px;">
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-filtros">
                    <div class="filtros-grid">
                        <input type="text" id="filtroColor" placeholder="Color" class="filtro-input">
                        <input type="text" id="filtroModelo" placeholder="Modelo" class="filtro-input">
                        <input type="text" id="filtroSerie" placeholder="Número de serie (VIN)" class="filtro-input">
                    </div>
                </div>
                <div id="listaVehiculos" class="modal-lista"></div>
                <div class="modal-footer">
                    <button id="cerrarModalVehiculos2" class="btn-cerrar">Cerrar</button>
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
                <div id="upgSpecs" style="margin-top:15px;"></div>
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

        {{-- Modal de paquetes --}}
        <div id="modalPaquetes" class="modal" style="display:none;">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h2>Paquetes de Seguro</h2>
                    <button type="button" class="close-modal" data-target="paquetes">&times;</button>
                </div>
                <div class="modal-body">
                    <h3 style="margin:8px 0 14px;">Paquetes (precio por día)</h3>
                    <div id="packGrid" class="cards">
                        @foreach ($seguros as $seguro)
                            <label class="card seguro-item" data-id="{{ $seguro->id_seguro }}"
                                data-precio="{{ $seguro->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $seguro->nombre }}</h4>
                                    <p>{{ $seguro->cobertura }}</p>
                                    <div class="precio">${{ number_format($seguro->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch {{ $seguro->id_seguro == ($seguroSeleccionado->id_seguro ?? null) ? 'on' : '' }}"
                                        data-id="{{ $seguro->id_seguro }}"></div>
                                    <div class="small" style="margin-top:8px;">Seleccionar Paquete</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de paquetes individuales --}}
        <div class="modal" id="modalIndividuales" style="display:none;">
            <div class="modal-content">
                <header class="modal-header">
                    <h3>Protecciones individuales</h3>
                    <button class="closeModal" data-target="individuales">&times;</button>
                </header>
                <section class="modal-body">
                    <div class="note" style="margin-bottom:14px;">
                        Selecciona una o varias protecciones individuales.
                    </div>
                    <h4 class="categoria-title">Colisión y robo</h4>
                    <div class="cards scroll-h">
                        @foreach ($grupo_colision as $ind)
                            <label class="card individual-item" data-id="{{ $ind->id_individual }}"
                                data-precio="{{ $ind->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $ind->nombre }}</h4>
                                    <p>{{ $ind->descripcion }}</p>
                                    <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                                    <div class="small">Incluir</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <h4 class="categoria-title">Gastos médicos</h4>
                    <div class="cards scroll-h">
                        @foreach ($grupo_medicos as $ind)
                            <label class="card individual-item" data-id="{{ $ind->id_individual }}"
                                data-precio="{{ $ind->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $ind->nombre }}</h4>
                                    <p>{{ $ind->descripcion }}</p>
                                    <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                                    <div class="small">Incluir</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <h4 class="categoria-title">Asistencia para el camino</h4>
                    <div class="cards scroll-h">
                        @foreach ($grupo_asistencia as $ind)
                            <label class="card individual-item" data-id="{{ $ind->id_individual }}"
                                data-precio="{{ $ind->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $ind->nombre }}</h4>
                                    <p>{{ $ind->descripcion }}</p>
                                    <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                                    <div class="small">Incluir</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <h4 class="categoria-title">Daños a terceros</h4>
                    <div class="cards scroll-h">
                        @foreach ($grupo_terceros as $ind)
                            <label class="card individual-item" data-id="{{ $ind->id_individual }}"
                                data-precio="{{ $ind->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $ind->nombre }}</h4>
                                    <p>{{ $ind->descripcion }}</p>
                                    <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                                    <div class="small">Incluir</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <h4 class="categoria-title">Protecciones automáticas</h4>
                    <div class="cards scroll-h">
                        @foreach ($grupo_protecciones as $ind)
                            <label class="card individual-item" data-id="{{ $ind->id_individual }}"
                                data-precio="{{ $ind->precio_por_dia }}">
                                <div class="body">
                                    <h4>{{ $ind->nombre }}</h4>
                                    <p>{{ $ind->descripcion }}</p>
                                    <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} MXN x Día</div>
                                    <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>
                                    <div class="small">Incluir</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>
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
