@extends('layouts.Ventas')
@section('Titulo', 'Contrato - Operación')

@section('css-vistaContrato')
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')

    <main class="main" id="contratoApp" data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
        data-id-reservacion="{{ $reservacion->id_reservacion }}">

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
                                    <p>Salida: <span id="detFechaSalida">{{ $fechaInicio->format('Y-m-d') }}</span> ·
                                        <span id="detHoraSalida">{{ $horaRetiro->format('h:i A') }}</span>
                                    </p>
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

        <nav class="stepper-navbar">
            <ul class="stepper-list">
                <li class="stepper-item completed" data-step-indicator="1">
                    <div class="stepper-circle">1</div>
                    <div class="stepper-title">Reservación</div>
                </li>
                <li class="stepper-line completed"></li>

                <li class="stepper-item completed" data-step-indicator="2">
                    <div class="stepper-circle">2</div>
                    <div class="stepper-title">Servicios</div>
                </li>
                <li class="stepper-line completed"></li>

                <li class="stepper-item completed" data-step-indicator="3">
                    <div class="stepper-circle">3</div>
                    <div class="stepper-title">Protecciones</div>
                </li>
                <li class="stepper-line completed"></li>

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

                {{-- Paso 4 --}}
                <article class="step" data-step="4">
                    <div class="body">
                        <form id="formDocumentacion" action="{{ route('contrato.guardarDocumentacion') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id_reservacion" value="{{ $idReservacion }}">
                            <input type="hidden" name="id_contrato" value="{{ $idContrato ?? '' }}">

                            {{-- TITULAR --}}
                            <div class="bloque-conductor-individual">
                                <section class="section">
                                    <div class="head">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            Documentación del Titular: {{ $reservacion->nombre_cliente ?? '' }}
                                            {{ $reservacion->apellidos_cliente ?? '' }}
                                        </span>
                                    </div>

                                    <div class="cnt">
                                        <input type="hidden" name="conductores[0][id_conductor]" value="">
                                        <input type="hidden" name="conductores[0][es_titular]" value="1">

                                        <div class="form-grid">
                                            <div class="input-row">
                                                <label>Tipo de Identificación</label>
                                                <select name="conductores[0][tipo_identificacion]" required>
                                                    <option value="" disabled selected>Selecciona una opción...
                                                    </option>
                                                    <option value="ine">Credencial para Votar (INE/IFE)</option>
                                                    <option value="pasaporte">Pasaporte</option>
                                                    <option value="cedula">Cédula Profesional</option>
                                                </select>
                                            </div>

                                            <div class="input-row">
                                                <label>Número de Identificación</label>
                                                <input name="conductores[0][numero_identificacion]" type="text"
                                                    placeholder="XXXX-XXXX-XXXX" maxlength="18" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Nombres</label>
                                                <input name="conductores[0][nombre]" type="text"
                                                    value="{{ $reservacion->nombre_cliente ?? '' }}" readonly
                                                    class="input-readonly">
                                            </div>

                                            <div class="input-row">
                                                <label>Apellido Paterno</label>
                                                <input name="conductores[0][apellido_paterno]" type="text"
                                                    value="{{ $reservacion->apellido_paterno ?? ($reservacion->apellidos_cliente ?? '') }}"
                                                    readonly class="input-readonly">
                                            </div>

                                            <div class="input-row">
                                                <label>Apellido Materno</label>
                                                <input name="conductores[0][apellido_materno]" type="text"
                                                    value="{{ $reservacion->apellido_materno ?? '' }}" readonly
                                                    class="input-readonly">
                                            </div>

                                            <div class="input-row">
                                                <label>Contacto de Emergencia</label>
                                                <input name="conductores[0][contacto_emergencia]" type="text"
                                                    maxlength="10" placeholder="Ej. 4421234567" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Fecha de Nacimiento</label>
                                                <input name="conductores[0][fecha_nacimiento]" type="date" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Fecha de Vencimiento del ID</label>
                                                <input name="conductores[0][fecha_vencimiento_id]" type="date"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="form-grid mt-12">
                                            <div>
                                                <label>Fotografía Identificación — Frente</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][idFrente]" type="file"
                                                        accept="image/*" required>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                        viewBox="0 0 24 24" fill="none" stroke="#9ca3af"
                                                        stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12"
                                                            y2="15"></line>
                                                    </svg>
                                                    <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                </div>
                                                <div class="preview"></div>
                                            </div>

                                            <div>
                                                <label>Fotografía Identificación — Reverso</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][idReverso]" type="file"
                                                        accept="image/*" required>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                        viewBox="0 0 24 24" fill="none" stroke="#9ca3af"
                                                        stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12"
                                                            y2="15"></line>
                                                    </svg>
                                                    <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                </div>
                                                <div class="preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                {{-- LICENCIA TITULAR --}}
                                <section class="section mt-18">
                                    <div class="head flex-head">
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                                <circle cx="12" cy="5" r="2"></circle>
                                                <path d="M12 7v4"></path>
                                                <line x1="8" y1="16" x2="8.01" y2="16">
                                                </line>
                                                <line x1="16" y1="16" x2="16.01" y2="16">
                                                </line>
                                            </svg>
                                            Licencia de Conducir (Titular)
                                        </span>

                                        <button type="button" id="btnInfoLicencia" class="btn-info-licencia"
                                            title="Aviso importante">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="16" x2="12" y2="12">
                                                </line>
                                                <line x1="12" y1="8" x2="12.01" y2="8">
                                                </line>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="cnt">
                                        <div class="form-grid">
                                            <div class="input-row">
                                                <label>Número de Licencia</label>
                                                <input name="conductores[0][numero_licencia]" type="text"
                                                    placeholder="Ej. QRO-123456" required>
                                            </div>

                                            <div class="input-row">
                                                <label>PAIS</label>
                                                <select name="conductores[0][id_pais]" required>
                                                    <option value="">Selecciona…</option>
                                                    <option value="MX">México</option>
                                                    <option value="US">U.S.A</option>
                                                    <option value="BR">Brasil</option>
                                                    <option value="CO">Colombia</option>
                                                    <option value="CA">Canadá</option>
                                                </select>
                                            </div>

                                            <div class="input-row">
                                                <label>Fecha de Emisión</label>
                                                <input name="conductores[0][fecha_emision]" type="date" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Fecha de Vencimiento de la Licencia</label>
                                                <input name="conductores[0][fecha_vencimiento]" type="date" required>
                                            </div>
                                        </div>

                                        <div class="form-grid mt-12">
                                            <div>
                                                <label>Licencia — Frente</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][licFrente]" type="file"
                                                        accept="image/*" required>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                        viewBox="0 0 24 24" fill="none" stroke="#9ca3af"
                                                        stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12"
                                                            y2="15"></line>
                                                    </svg>
                                                    <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                </div>
                                                <div class="preview"></div>
                                            </div>

                                            <div>
                                                <label>Licencia — Reverso</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][licReverso]" type="file"
                                                        accept="image/*" required>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                        viewBox="0 0 24 24" fill="none" stroke="#9ca3af"
                                                        stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                        <line x1="12" y1="3" x2="12"
                                                            y2="15"></line>
                                                    </svg>
                                                    <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                </div>
                                                <div class="preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            {{-- CONDUCTORES ADICIONALES --}}
                            @foreach ($conductoresExtras as $index => $extra)
                                @php $idx = $index + 1; @endphp

                                <div class="bloque-conductor-individual bloque-conductor-adicional">
                                    <section class="section">
                                        <div class="head bg-slate">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M19 8v6"></path>
                                                    <path d="M22 11h-6"></path>
                                                </svg>
                                                Documentación Conductor Adicional: {{ $extra['nombres'] }}
                                            </span>
                                        </div>

                                        <div class="cnt">
                                            <input type="hidden" name="conductores[{{ $idx }}][id_conductor]"
                                                value="{{ $extra['id_conductor'] }}">
                                            <input type="hidden" name="conductores[{{ $idx }}][es_titular]"
                                                value="0">

                                            <div class="form-grid">
                                                <div class="input-row">
                                                    <label>Tipo de Identificación</label>
                                                    <select name="conductores[{{ $idx }}][tipo_identificacion]"
                                                        required>
                                                        <option value="ine">INE/IFE</option>
                                                        <option value="pasaporte">Pasaporte</option>
                                                        <option value="cedula">Cédula</option>
                                                    </select>
                                                </div>

                                                <div class="input-row">
                                                    <label>Número de Identificación</label>
                                                    <input name="conductores[{{ $idx }}][numero_identificacion]"
                                                        type="text" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Nombres</label>
                                                    <input name="conductores[{{ $idx }}][nombre]" type="text"
                                                        value="{{ $extra['nombres'] }}" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Apellido Paterno</label>
                                                    <input name="conductores[{{ $idx }}][apellido_paterno]"
                                                        type="text" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Apellido Materno</label>
                                                    <input name="conductores[{{ $idx }}][apellido_materno]"
                                                        type="text" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Contacto de Emergencia</label>
                                                    <input name="conductores[{{ $idx }}][contacto_emergencia]"
                                                        type="text" maxlength="10" placeholder="Ej. 4421234567">
                                                </div>

                                                <div class="input-row">
                                                    <label>Fecha de Nacimiento</label>
                                                    <input name="conductores[{{ $idx }}][fecha_nacimiento]"
                                                        type="date" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Fecha de Vencimiento del ID</label>
                                                    <input name="conductores[{{ $idx }}][fecha_vencimiento_id]"
                                                        type="date" required>
                                                </div>
                                            </div>

                                            <div class="form-grid mt-12">
                                                <div>
                                                    <label>Identificación — Frente</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][idFrente]"
                                                            type="file" accept="image/*" required>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                            height="28" viewBox="0 0 24 24" fill="none"
                                                            stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="17 8 12 3 7 8"></polyline>
                                                            <line x1="12" y1="3" x2="12"
                                                                y2="15"></line>
                                                        </svg>
                                                        <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>

                                                <div>
                                                    <label>Identificación — Reverso</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][idReverso]"
                                                            type="file" accept="image/*" required>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                            height="28" viewBox="0 0 24 24" fill="none"
                                                            stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="17 8 12 3 7 8"></polyline>
                                                            <line x1="12" y1="3" x2="12"
                                                                y2="15"></line>
                                                        </svg>
                                                        <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- LICENCIA ADICIONAL --}}
                                    <section class="section mt-18">
                                        <div class="head bg-slate-light">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                                <circle cx="12" cy="5" r="2"></circle>
                                                <path d="M12 7v4"></path>
                                                <line x1="8" y1="16" x2="8.01" y2="16">
                                                </line>
                                                <line x1="16" y1="16" x2="16.01" y2="16">
                                                </line>
                                            </svg>
                                            Licencia de Conducir (Adicional)
                                        </div>

                                        <div class="cnt">
                                            <div class="form-grid">
                                                <div class="input-row">
                                                    <label>Número de Licencia</label>
                                                    <input name="conductores[{{ $idx }}][numero_licencia]"
                                                        type="text" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>PAIS</label>
                                                    <select name="conductores[{{ $idx }}][id_pais]" required>
                                                        <option value="">Selecciona…</option>
                                                        <option value="MX">México</option>
                                                        <option value="US">U.S.A</option>
                                                        <option value="BR">Brasil</option>
                                                        <option value="CO">Colombia</option>
                                                        <option value="CA">Canadá</option>
                                                    </select>
                                                </div>

                                                <div class="input-row">
                                                    <label>Fecha de Emisión</label>
                                                    <input name="conductores[{{ $idx }}][fecha_emision]"
                                                        type="date" required>
                                                </div>

                                                <div class="input-row">
                                                    <label>Fecha de Vencimiento de la Licencia</label>
                                                    <input name="conductores[{{ $idx }}][fecha_vencimiento]"
                                                        type="date" required>
                                                </div>
                                            </div>

                                            <div class="form-grid mt-12">
                                                <div>
                                                    <label>Licencia — Frente</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][licFrente]"
                                                            type="file" accept="image/*" required>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                            height="28" viewBox="0 0 24 24" fill="none"
                                                            stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="17 8 12 3 7 8"></polyline>
                                                            <line x1="12" y1="3" x2="12"
                                                                y2="15"></line>
                                                        </svg>
                                                        <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>

                                                <div>
                                                    <label>Licencia — Reverso</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][licReverso]"
                                                            type="file" accept="image/*" required>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                            height="28" viewBox="0 0 24 24" fill="none"
                                                            stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="17 8 12 3 7 8"></polyline>
                                                            <line x1="12" y1="3" x2="12"
                                                                y2="15"></line>
                                                        </svg>
                                                        <div class="msg">Haz clic o arrastra la imagen aquí</div>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            @endforeach

                            {{-- BOTONES DE NAVEGACIÓN Y GUARDADO --}}
                            <div class="acciones-finales">
                                <button class="btn gray" id="back_to_step3" type="button">← Atrás</button>

                                <div class="btn-group">
                                    <button class="btn primary" id="btnContinuarDoc" type="submit">Guardar y Revisar
                                        →</button>
                                    <button class="btn success btn-saltar" id="btnSaltarDoc" type="button">Continuar a
                                        Revisión →</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </article>

                {{-- Paso 5 --}}
                <article class="step" data-step="5">
                    <div class="body">
                        <div class="contrato-preview-container">

                            <h2 class="preview-title">
                                Contrato <span
                                    id="res-numero-contrato">{{ $contrato?->numero_contrato ?? 'PREVIEW' }}</span>
                            </h2>

                            <div class="preview-grid">
                                {{-- DATOS BÁSICOS --}}
                                <div class="preview-col">
                                    <div class="preview-card">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                            Conductores
                                        </h5>
                                        <div id="res-lista-conductores">
                                            <p class="txt-primary">
                                                {{ $reservacion->nombre_cliente }} {{ $reservacion->apellidos_cliente }}
                                                <span class="txt-muted">(Titular)</span>
                                            </p>
                                            @if (isset($conductoresExtras) && count($conductoresExtras) > 0)
                                                @foreach ($conductoresExtras as $extra)
                                                    <p class="txt-secondary">
                                                        • {{ $extra['nombres'] ?? 'Conductor' }}
                                                        {{ $extra['apellidos'] ?? '' }}
                                                        <span class="txt-muted">(Adicional)</span>
                                                    </p>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    <div class="preview-card">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            Oficina
                                        </h5>
                                        <div class="loc-block">
                                            <label>ENTREGA</label>
                                            <p><b>{{ $reservacion->sucursal_retiro_nombre ?? 'Sucursal no asignada' }}</b>
                                            </p>
                                            <p class="loc-date">
                                                {{ \Carbon\Carbon::parse($reservacion->fecha_inicio)->format('d M Y - h:i A') }}
                                            </p>
                                        </div>
                                        <div class="loc-block">
                                            <label>DEVOLUCIÓN</label>
                                            <p><b>{{ $reservacion->sucursal_entrega_nombre ?? 'Sucursal no asignada' }}</b>
                                            </p>
                                            <p class="loc-date">
                                                {{ \Carbon\Carbon::parse($reservacion->fecha_fin)->format('d M Y - h:i A') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="preview-card">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <path
                                                    d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a2 2 0 0 0-1.6-.8H5.32a2 2 0 0 0-1.69.94L1 12v4h3m4 0h6m-6 0a2 2 0 1 1-4 0m10 0a2 2 0 1 1-4 0">
                                                </path>
                                            </svg>
                                            Auto
                                        </h5>
                                        <p id="res-auto-nombre" class="txt-primary">
                                            {{ $vehiculo->marca ?? '' }}
                                            {{ $vehiculo->modelo ?? 'Vehículo Seleccionado' }}
                                        </p>
                                        <p class="txt-secondary text-sm">VIN: <span
                                                id="res-auto-vin">{{ $vehiculo->numero_serie ?? '---' }}</span></p>
                                        <p class="txt-secondary text-sm">Placa: <span
                                                id="res-auto-placa">{{ $vehiculo->placa ?? '---' }}</span></p>
                                    </div>
                                </div>

                                {{-- COBERTURAS Y PAGOS --}}
                                <div class="preview-col">
                                    <div class="preview-card">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            </svg>
                                            Coberturas
                                        </h5>
                                        <ul id="res-lista-coberturas" class="res-list">
                                            <li class="txt-muted">Cargando protecciones...</li>
                                        </ul>
                                    </div>

                                    <div class="preview-card">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2"></rect>
                                                <line x1="12" y1="8" x2="12" y2="16">
                                                </line>
                                                <line x1="8" y1="12" x2="16" y2="12">
                                                </line>
                                            </svg>
                                            Extras
                                        </h5>
                                        <ul id="res-lista-extras" class="res-list">
                                            <li class="txt-muted">Cargando adicionales...</li>
                                        </ul>
                                    </div>

                                    <div class="preview-card highlight-pago">
                                        <h5>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b;">
                                                <line x1="12" y1="1" x2="12" y2="23">
                                                </line>
                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                            </svg>
                                            Pago Estimado
                                        </h5>
                                        <div
                                            style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center; margin: 12px 0;">
                                            <label class="pago-label" style="margin-bottom: 4px;">TOTAL A PAGAR</label>
                                            <h4 id="res-total-final-p5" class="pago-total"
                                                style="color: var(--brand, #FF1E2D);">$0.00 MXN</h4>
                                        </div>
                                        <p class="pago-disclaimer">
                                            Todos los cargos son sólo aproximados, sujetos a cambios si el vehículo no es
                                            devuelto en el lugar y fecha especificados, o si existen daños o accesorios
                                            faltantes al momento de la devolución.
                                        </p>
                                    </div>
                                </div>

                                {{-- FIRMA Y TEXTO LEGAL --}}
                                <div class="preview-col">
                                    <div class="signature-section">
                                        <div class="signature-pad-wrapper"
                                            style="border: 2px dashed #cbd5e1; border-radius: 10px; height: 200px; position: relative;">

                                            <canvas id="padPaso5"
                                                style="touch-action: none; width: 100%; height: 100%; display: block;"></canvas>

                                            <button type="button" id="clearPaso5"
                                                style="position: absolute; bottom: 10px; right: 10px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer; color: #64748b; z-index: 10;">
                                                Limpiar Firma
                                            </button>
                                        </div>

                                        <p class="txt-primary mb-2" style="margin-top: 15px;">
                                            {{ $reservacion->nombre_cliente }} {{ $reservacion->apellidos_cliente }}
                                        </p>
                                        <p class="txt-muted mb-15">Firma de aceptación del contrato</p>

                                        {{-- Input oculto --}}
                                        <input type="hidden" id="firma_cliente_paso5" name="firma_cliente_paso5">

                                        <div class="legal-text">
                                            Al firmar acepto plenamente las obligaciones descritas en la carátula y en el
                                            clausulado de este contrato, así como las coberturas y servicios contratados.
                                            Declaro haber recibido el auto en las condiciones descritas y acepto las
                                            condiciones del reverso de este documento, así como el aviso de privacidad que
                                            se encuentra a mi disposición en:
                                            <br><br>
                                            <a href="https://www.europcar.com.mx/privacidad.php" target="_blank"
                                                class="privacy-link">Aviso de privacidad</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="acciones-finales">
                                <button class="btn gray" id="back4" type="button">← Corregir Documentos</button>
                                <button class="btn-continuar-yellow" id="go6" type="button">Continuar →</button>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- BLOQUE OCULTO: Fuente de datos para la Tablet (inline limpiado) --}}
                <div id="fuente-datos-contrato">
                    <div id="fuente-seguros">
                        @forelse($segurosSeleccionados ?? [] as $s)
                            <li class="fuente-li">
                                <span>{{ $s->nombre }}</span>
                                <b>${{ number_format($s->monto, 2) }}</b>
                            </li>
                        @empty
                            <li>Protección Básica (TPL)</li>
                        @endforelse
                    </div>

                    <div id="fuente-extras">
                        @if (isset($cargoGas) && $cargoGas)
                            <li>Gasolina Prepago</li>
                        @endif
                        @if (isset($cargoDrop) && $cargoDrop)
                            <li>Servicio Dropoff</li>
                        @endif
                        @foreach ($serviciosExtras ?? [] as $se)
                            <li class="fuente-li">
                                <span>{{ $se['nombre'] ?? $se->nombre }} (x{{ $se['cantidad'] ?? $se->cantidad }})</span>
                                <b>${{ number_format($se['monto'] ?? ($se->monto ?? 0), 2) }}</b>
                            </li>
                        @endforeach
                    </div>
                </div>

                {{-- Paso 6 --}}
                <article class="step" data-step="6">
                    <div class="body">
                        <section class="section">
                            <div class="head">Detalle de Cargos Finales</div>
                            <div class="cnt">
                                {{-- RENTA DEL VEHÍCULO --}}
                                <div class="row row-border-bottom row-pb-12">
                                    <div>
                                        <span class="row-label">Renta de Vehículo</span>
                                        <small id="baseDescr" class="row-small">3 días · $600.00</small>
                                    </div>
                                    <div id="baseAmt" class="row-amount">$1,800.00</div>
                                </div>

                                {{-- PROTECCIONES --}}
                                <div class="row row-protection">
                                    <div>
                                        <span class="row-label">Protecciones y Coberturas</span>
                                        <small id="insDescr" class="row-small">LDW PACK (3 días · $1,120.00)</small>
                                    </div>
                                    <div id="insAmt" class="row-amount-green">$3,360.00</div>
                                </div>

                                {{-- EXTRAS --}}
                                <div id="listaExtrasP6"></div>

                                {{-- TOTALIZACIÓN --}}
                                <div class="totalization-box">
                                    <div class="row">
                                        <div class="totalization-label">Subtotal</div>
                                        <div id="subtotalAmt" class="totalization-value">$5,760.00</div>
                                    </div>
                                    <div class="row row-mt-5">
                                        <div class="totalization-label">IVA (16%)</div>
                                        <div id="ivaOnly" class="totalization-value">$921.60</div>
                                    </div>
                                    <div class="row row-total-final">
                                        <div class="total-final-label">TOTAL CONTRATO</div>
                                        <div id="totalContrato" class="total-final-value">$6,681.60</div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="section section-mt-16">
                            <div class="head">Estado de Cuenta</div>
                            <div class="cnt">
                                <div class="flex-between-wrap">
                                    <div>
                                        <div class="small">Total del Contrato</div>
                                        <div class="total" id="detTotalFinalCuenta">$0.00 MXN</div>
                                    </div>
                                    <div>
                                        <div class="small">Saldo Pendiente</div>
                                        <div class="badge badge-saldo" id="detSaldo">$0.00 MXN</div>
                                    </div>
                                </div>

                                <div class="garantia-box">
                                    <div>
                                        <div class="small">Garantía / Preautorización</div>
                                        <div id="detGarantiaSeguro" class="garantia-monto">$0.00 MXN</div>
                                        <div id="detGarantiaSeguroMeta" class="garantia-meta">Sin paquete</div>
                                        <div id="detGarantiaSeguroStatus" class="garantia-status">Pendiente: $0.00 MXN
                                        </div>
                                    </div>
                                </div>

                                <h3 class="historial-title">Historial de Pagos</h3>
                                <table class="table table-pagos" id="tblPagos">
                                    <thead>
                                        <tr class="table-header-row">
                                            <th class="th-left">#</th>
                                            <th class="th-left">Fecha</th>
                                            <th class="th-left">Método</th>
                                            <th class="th-left">Origen</th>
                                            <th class="th-right">Monto</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="payBody">
                                        <tr>
                                            <td colspan="6" class="table-loading">Cargando historial de pagos...</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="pagos-footer">
                                    <div class="pagos-realizados">
                                        Pagos realizados: <b id="detPagos" class="pagos-realizados-valor">$0.00</b>
                                    </div>
                                    <button id="btnAdd" class="btn-registrar-pago">+ Registrar Pago</button>
                                </div>
                            </div>
                        </section>

                        <div class="acciones acciones-mt-20">
                            <button class="btn gray" id="back5" type="button">← Volver a Revisión</button>
                            <form id="formFinalizarContrato" action="{{ route('contrato.finalizar', $idReservacion) }}"
                                method="POST">
                                @csrf
                                <button type="submit" class="btn-finalizar">Finalizar Contrato y Generar PDF →</button>
                            </form>
                        </div>
                    </div>
                </article>

            </section>

        </div>

        {{-- Modal Pagos --}}
        <div class="modal-back" id="mb">
            <div class="modal modal-pagos">
                <div class="head">
                    Registrar Pago
                    <button id="mx" class="btn gray btn-close-modal">✕</button>
                </div>
                <div class="body">
                    <div class="pay-groups" id="payTabs">
                        <button class="tab active" data-tab="paypal">PayPal</button>
                        <button class="tab" data-tab="tarjeta">Terminal</button>
                        <button class="tab" data-tab="efectivo">Efectivo</button>
                        <button class="tab" data-tab="transferencia">Transferencia / Depósito</button>
                    </div>
                    <div id="pFlowHint" class="small flow-hint">
                        Pago 1 de 2: registra primero el monto de la reservación.
                    </div>
                    <div id="methods">
                        <div data-pane="paypal">
                            <p class="small">Al seleccionar PayPal, se abrirá la pasarela en línea.</p>
                            <div class="paypal-box">
                                <div id="paypal-button-container-modal"></div>
                            </div>
                        </div>
                        <div data-pane="tarjeta" class="pane-hidden">
                            <div class="method-grid">
                                <label class="mcard"><input type="radio" name="m" value="VISA"><img
                                        src="../assets/media/visa.png" alt="">
                                    <div>
                                        <div class="ttl">VISA</div>
                                        <div class="sub">Terminal</div>
                                    </div>
                                </label>
                                <label class="mcard"><input type="radio" name="m" value="MASTERCARD"><img
                                        src="../assets/media/master.jpg" alt="">
                                    <div>
                                        <div class="ttl">Mastercard</div>
                                        <div class="sub">Terminal</div>
                                    </div>
                                </label>
                                <label class="mcard"><input type="radio" name="m" value="AMEX"><img
                                        src="../assets/media/amex.png" alt="">
                                    <div>
                                        <div class="ttl">AMEX</div>
                                        <div class="sub">Terminal</div>
                                    </div>
                                </label>
                                <label class="mcard"><input type="radio" name="m" value="DEBITO"><img
                                        src="../assets/media/debito.png" alt="">
                                    <div>
                                        <div class="ttl">Débito</div>
                                        <div class="sub">Terminal</div>
                                    </div>
                                </label>
                            </div>
                            <div class="upload-section">
                                <label>Foto del ticket (obligatorio)</label>
                                <input id="fileTerminal" type="file" accept="image/*,.pdf">
                            </div>
                        </div>
                        <div data-pane="efectivo" class="pane-hidden">
                            <p class="small">Se generará automáticamente un ticket interno.</p>
                        </div>
                        <div data-pane="transferencia" class="pane-hidden">
                            <div class="method-grid">
                                <label class="mcard"><input type="radio" name="m" value="TRANSFERENCIA"><img
                                        src="../assets/media/transfe.jpg" alt="">
                                    <div>
                                        <div class="ttl">Transferencia</div>
                                    </div>
                                </label>
                                <label class="mcard"><input type="radio" name="m" value="SPEI"><img
                                        src="../assets/media/spei.png" alt="">
                                    <div>
                                        <div class="ttl">SPEI</div>
                                    </div>
                                </label>
                                <label class="mcard"><input type="radio" name="m" value="DEPOSITO"><img
                                        src="../assets/media/deposito.png" alt="">
                                    <div>
                                        <div class="ttl">Depósito</div>
                                    </div>
                                </label>
                            </div>
                            <div class="upload-section">
                                <label>Comprobante del pago (obligatorio)</label>
                                <input id="fileTransfer" type="file" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>

                    <fieldset class="payment-detail-fieldset">
                        <legend>Detalle del pago</legend>
                        <div class="form-grid">
                            <div>
                                <label>Tipo de Pago</label>
                                <select id="pTipo">
                                    <option value="PAGO RESERVACIÓN">PAGO RESERVACIÓN</option>
                                    <option value="ANTICIPO">ANTICIPO</option>
                                    <option value="DEPÓSITO">DEPÓSITO</option>
                                    <option value="LIQUIDACIÓN">LIQUIDACIÓN</option>
                                    <option value="GARANTIA">GARANTIA / PREAUTORIZACION</option>
                                </select>
                            </div>
                            <div>
                                <label>Monto</label>
                                <input id="pMonto" type="number" step="0.01" min="0" placeholder="0.00">
                                <div class="err" id="pErr"></div>
                            </div>
                            <div class="full-width">
                                <label>Notas (opcional)</label>
                                <textarea id="pNotes" rows="2"></textarea>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="foot">
                    <button id="pSave" class="btn primary">GUARDAR PAGO</button>
                </div>
            </div>
        </div>

    </main>
@endsection

@section('js-vistaContrato')
    <script>
        window.ID_RESERVACION = "{{ $idReservacion }}";
        window.ID_CONTRATO = "{{ $idContrato ?? '' }}";
        window.csrfToken = "{{ csrf_token() }}";

        window.ID_SERVICIO_MENOR = {{ $idServicioMenor ?? 0 }};

        window.contratoId = window.ID_CONTRATO;
        window.clienteContratoUrl = "{{ route('contrato.obtenerCliente', $idContrato ?? 0) }}";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="{{ asset('js/ContratoGlobal.js') }}" defer></script>
    <script src="{{ asset('js/contrato2.js') }}" defer></script>
@endsection
