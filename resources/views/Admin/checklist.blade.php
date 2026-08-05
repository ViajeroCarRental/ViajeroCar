@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.Ventas')

@section('Titulo', 'Check List – Inspección')

@section('css-vistareservacionesAdmin')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/checklist.css') }}">
@endsection

@section('contenidoreservacionesAdmin')

@php
    $niveles = [
        "0","1/16","2/16","3/16","1/4","5/16","6/16",
        "7/16","1/2","9/16","10/16","11/16",
        "3/4","13/16","14/16","15/16","1"
    ];
@endphp
@php
    $modo = $modo ?? 'salida';
    $from = request()->get('from');
@endphp
<input type="hidden" id="idContrato" value="{{ $contrato->id_contrato }}">

<div class="checklist-container">

    {{-- ENCABEZADO --}}
    <header class="cl-header">
        <div class="cl-logo">
            <img src="/img/Logotipo Fondo.jpg" alt="Logo Viajero">
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

    {{-- DATOS DEL VEHICULO --}}
    <section class="paper-section vehicle-section">
        <h3 class="sec-title">Datos del vehículo</h3>

        <div class="vehicle-table-wrapper">
            <table class="vehicle-table">
                <colgroup>
                    <col style="width: 8%;">
                    <col style="width: 10%;">
                    <col style="width: 8%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 10%;">
                    <col style="width: 11%;">
                    <col style="width: 8%;">
                    <col style="width: 12%;">
                    <col style="width: 10%;">
                </colgroup>

                <tbody>
                    <tr>
                        <th>TIPO</th>
                        <td>{{ $tipo ?? '—' }}</td>
                        <th>MODELO</th>
                        <td>{{ $modelo ?? '—' }}</td>
                        <th>PLACAS</th>
                        <td>{{ $placas ?? '—' }}</td>
                        <th>COLOR</th>
                        <td>{{ $color ?? '—' }}</td>
                        <th>TRANSMISIÓN</th>
                        <td>{{ $transmision ?? '—' }}</td>
                    </tr>

                    <tr>
                        <th>CD. QUE<br>ENTREGA</th>
                        <td>{{ $ciudadEntrega ?? '—' }}</td>
                        <th>CD. QUE<br>RECIBE</th>
                        <td>{{ $ciudadRecibe ?? '—' }}</td>
                        <th>KILOMETRAJE<br>SALIDA</th>
                        <td class="km-cell">
                            <span id="kmSalidaText">{{ $kmSalida ?? '—' }}</span>
                            <input type="number" id="kmSalidaInput" value="{{ $kmSalida ?? '' }}" min="0">
                            <button type="button" id="btnGuardarKmSalida" class="btn-guardar-km">Guardar</button>
                        </td>
                        <th>PROTECCIÓN</th>
                        <td colspan="3" class="protection-cell">{{ $proteccion ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- GASOLINA --}}
    <section class="paper-section gas-wrap">
        <h3 class="sec-title center">Gasolina – Inspección</h3>

        <div class="fuel-grid">

            {{-- SALIDA --}}
            <div class="fuel-card">
                <h4 class="fuel-title">Gasolina – Salida</h4>

                <div class="gas-pill">
                    <span>Nivel seleccionado:</span>
                    <strong id="gasSalidaTxt">—</strong>
                </div>

                <div class="fuel-gauge">
                    <svg viewBox="0 0 200 120" class="fuel-svg">
                        <defs>
                            <linearGradient id="arcSalidaColor" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#dc2626"/>
                                <stop offset="30%" stop-color="#f97316"/>
                                <stop offset="50%" stop-color="#facc15"/>
                                <stop offset="75%" stop-color="#22c55e"/>
                                <stop offset="100%" stop-color="#d1d5db"/>
                            </linearGradient>
                        </defs>
                        <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="22" stroke-linecap="round"/>
                        <path id="arcSalida" d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="url(#arcSalidaColor)" stroke-width="22" stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="283"/>
                        <line id="needleSalida" x1="100" y1="100" x2="100" y2="32" stroke="#0f172a" stroke-width="4" stroke-linecap="round" style="transform-origin:100px 100px; transform:rotate(-90deg); transition:.45s ease;" />
                        <circle cx="100" cy="100" r="7" fill="#0f172a"/>
                        <text x="26" y="110" class="gauge-label">E</text>
                        <text x="100" y="28" class="gauge-label">1/2</text>
                        <text x="174" y="110" class="gauge-label">F</text>
                    </svg>
                </div>

                <label class="fuel-label">Seleccionar nivel</label>
                <select id="selectGasSalida" class="fuel-select" data-inicial="{{ $gasolinaSalida ?? '' }}" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    <option value="">—</option>
                    @foreach($niveles as $i => $n)
                        <option value="{{ $n }}" data-pct="{{ round(($i/(count($niveles)-1))*100) }}" class="{{ in_array((string) $n, ['0', '1/4', '1/2', '3/4', '1'], true) ? 'fuel-option-bold' : '' }}">
                            {{ $n }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- RECIBIDO --}}
            <div class="fuel-card">
                <h4 class="fuel-title">Gasolina – Recibido</h4>

                <div class="gas-pill">
                    <span>Nivel seleccionado:</span>
                    <strong id="gasRecibeTxt">—</strong>
                </div>

                <div class="fuel-gauge">
                    <svg viewBox="0 0 200 120" class="fuel-svg">
                        <defs>
                            <linearGradient id="arcRecibeColor" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#dc2626"/>
                                <stop offset="30%" stop-color="#f97316"/>
                                <stop offset="50%" stop-color="#facc15"/>
                                <stop offset="75%" stop-color="#22c55e"/>
                                <stop offset="100%" stop-color="#d1d5db"/>
                            </linearGradient>
                        </defs>
                        <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="#e5e7eb" stroke-width="22" stroke-linecap="round"/>
                        <path id="arcRecibe" d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="url(#arcRecibeColor)" stroke-width="22" stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="283"/>
                        <line id="needleRecibe" x1="100" y1="100" x2="100" y2="32" stroke="#0f172a" stroke-width="4" stroke-linecap="round" style="transform-origin:100px 100px; transform:rotate(-90deg); transition:.45s ease;" />
                        <circle cx="100" cy="100" r="7" fill="#0f172a"/>
                        <text x="26" y="110" class="gauge-label">E</text>
                        <text x="100" y="28" class="gauge-label">1/2</text>
                        <text x="174" y="110" class="gauge-label">F</text>
                    </svg>
                </div>

                <label class="fuel-label">Seleccionar nivel</label>
                <select id="selectGasRecibe" class="fuel-select" data-inicial="{{ $gasolinaRegreso ?? '' }}" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    <option value="">—</option>
                    @foreach($niveles as $i => $n)
                        <option value="{{ $n }}" data-pct="{{ round(($i/(count($niveles)-1))*100) }}" class="{{ in_array((string) $n, ['0', '1/4', '1/2', '3/4', '1'], true) ? 'fuel-option-bold' : '' }}">
                            {{ $n }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </section>

    {{-- DIAGRAMA --}}
    <section class="paper-section">
        <h3 class="sec-title center">Auto</h3>
        <div class="diagram-card">
            @include('components.diagram-car')
        </div>
    </section>

    {{-- IMAGENES GENERALES --}}
    <section class="paper-section">
        <h3 class="sec-title center">Imágenes generales del vehículo</h3>

        <div class="photo-grid">

            {{-- SALIDA --}}
            <div class="photo-column">
                <h4 class="photo-column-title">SALIDA</h4>

                <div class="photo-slot">
                    <div class="photo-slot-label">1. FRENTE</div>
                    <div class="photo-uploader" data-name="frenteSalida" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="frente_salida" accept="image/*" capture="environment" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-frenteSalida">
                        @if(!empty($fotosSalida['frente']))
                        <div class="thumb" data-foto-id="{{ $fotosSalida['frente']->id_inspeccion_fc }}">
                            <img src="data:{{ $fotosSalida['frente']->mime_type }};base64,{{ base64_encode($fotosSalida['frente']->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">2. PARABRISAS</div>
                    <div class="photo-uploader" data-name="parabrisasSalida" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="parabrisas_salida" accept="image/*" capture="environment" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-parabrisasSalida">
                        @if(!empty($fotosSalida['parabrisas']))
                        <div class="thumb" data-foto-id="{{ $fotosSalida['parabrisas']->id_inspeccion_fc }}">
                            <img src="data:{{ $fotosSalida['parabrisas']->mime_type }};base64,{{ base64_encode($fotosSalida['parabrisas']->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">3. LADO CONDUCTOR</div>
                    <div class="photo-uploader" data-name="ladoConductorSalida" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="lado_conductor_salida" accept="image/*" capture="environment" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-ladoConductorSalida">
                        @if(!empty($fotosSalida['lado_conductor']))
                        <div class="thumb" data-foto-id="{{ $fotosSalida['lado_conductor']->id_inspeccion_fc }}">
                            <img src="data:{{ $fotosSalida['lado_conductor']->mime_type }};base64,{{ base64_encode($fotosSalida['lado_conductor']->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">4. LADO PASAJERO</div>
                    <div class="photo-uploader" data-name="ladoPasajeroSalida" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="lado_pasajero_salida" accept="image/*" capture="environment" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-ladoPasajeroSalida">
                        @if(!empty($fotosSalida['lado_pasajero']))
                        <div class="thumb" data-foto-id="{{ $fotosSalida['lado_pasajero']->id_inspeccion_fc }}">
                            <img src="data:{{ $fotosSalida['lado_pasajero']->mime_type }};base64,{{ base64_encode($fotosSalida['lado_pasajero']->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">5. ATRÁS</div>
                    <div class="photo-uploader" data-name="atrasSalida" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="atras_salida" accept="image/*" capture="environment" {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-atrasSalida">
                        @if(!empty($fotosSalida['atras']))
                        <div class="thumb" data-foto-id="{{ $fotosSalida['atras']->id_inspeccion_fc }}">
                            <img src="data:{{ $fotosSalida['atras']->mime_type }};base64,{{ base64_encode($fotosSalida['atras']->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">6. INTERIORES <span class="photo-hint">(máx. 8 fotos)</span></div>
                    <div class="photo-uploader" data-name="interioresSalida" data-max-files="8">
                        <span class="photo-uploader-msg">Toca para tomar fotos o elegir de la galería</span>
                        <input type="file" name="interiores_salida[]" accept="image/*" capture="environment" multiple {{ $modo === 'regreso' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-interioresSalida">
                        @if(!empty($fotosSalida['interiores']))
                        @foreach($fotosSalida['interiores'] as $foto)
                        <div class="thumb" data-foto-id="{{ $foto->id_inspeccion_fc }}">
                            <img src="data:{{ $foto->mime_type }};base64,{{ base64_encode($foto->archivo) }}">
                            <button type="button" class="rm rm-server" title="Eliminar">×</button>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- REGRESO --}}
            <div class="photo-column">
                <h4 class="photo-column-title">REGRESO</h4>

                <div class="photo-slot">
                    <div class="photo-slot-label">1. FRENTE</div>
                    <div class="photo-uploader" data-name="frenteRegreso" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="frente_regreso" accept="image/*" capture="environment" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-frenteRegreso"></div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">2. PARABRISAS</div>
                    <div class="photo-uploader" data-name="parabrisasRegreso" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="parabrisas_regreso" accept="image/*" capture="environment" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-parabrisasRegreso"></div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">3. LADO CONDUCTOR</div>
                    <div class="photo-uploader" data-name="ladoConductorRegreso" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="lado_conductor_regreso" accept="image/*" capture="environment" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-ladoConductorRegreso"></div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">4. LADO PASAJERO</div>
                    <div class="photo-uploader" data-name="ladoPasajeroRegreso" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="lado_pasajero_regreso" accept="image/*" capture="environment" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-ladoPasajeroRegreso"></div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">5. ATRÁS</div>
                    <div class="photo-uploader" data-name="atrasRegreso" data-max-files="1">
                        <span class="photo-uploader-msg">Toca para tomar foto o elegir de la galería</span>
                        <input type="file" name="atras_regreso" accept="image/*" capture="environment" {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-atrasRegreso"></div>
                </div>

                <div class="photo-slot">
                    <div class="photo-slot-label">6. INTERIORES <span class="photo-hint">(máx. 8 fotos)</span></div>
                    <div class="photo-uploader" data-name="interioresRegreso" data-max-files="8">
                        <span class="photo-uploader-msg">Toca para tomar fotos o elegir de la galería</span>
                        <input type="file" name="interiores_regreso[]" accept="image/*" capture="environment" multiple {{ $modo === 'salida' ? 'disabled' : '' }}>
                    </div>
                    <div class="photo-preview" id="prev-interioresRegreso"></div>
                </div>
            </div>

        </div>
    </section>

    {{-- ACEPTACION --}}
    <section class="paper-section">
        <p class="legal-text">
            {{ $leyendaSeguro ?? 'He verificado que el vehículo lleva el equipo especial especificado. Que los daños están marcados en imagen de auto y no soy responsable por daños o robo parcial o total; salvo una negligencia.' }}
        </p>
    </section>

    {{-- POSIBLES CARGOS --}}
    <section class="paper-section">
        <h3 class="sec-title">Información de posibles cargos</h3>

        <ol class="rules-list">
            <li>No se permite Fumar dentro de la unidad.</li>
            <li>No se permite manchar interior/exterior con sustancias químicas u orgánicas.</li>
            <li>No se permite el uso de huachicol ni combustibles diferentes a gasolina Premium.</li>
            <li>No se permite el cambio de piezas originales con las que se renta la unidad.</li>
        </ol>

        <div class="accept-line">
            <span>Acepto:</span>
            @if($contrato->firma_cliente)
                <img src="{{ $contrato->firma_cliente }}" class="firma-img" alt="Firma del cliente">
            @else
                <button class="btn-open-sign" data-type="cliente">Firmar Cliente</button>
            @endif
        </div>
    </section>

    {{-- COMENTARIOS --}}
    <section class="paper-section">
        <h3 class="sec-title">Comentario</h3>
        <textarea class="comment-input" data-field="comentario_cliente" placeholder="Escribe comentarios aquí..."></textarea>

        <h3 class="sec-title">Daños Interiores</h3>
        <textarea class="comment-input" data-field="danos_interiores" placeholder="Describe los daños interiores..."></textarea>
    </section>

    {{-- FIRMAS --}}
    <section class="paper-section">

        <p class="legal-text">
            Por el presente acuse, recibo este vehículo en las condiciones descritas anteriormente
            y me comprometo a notificar a un representante de Viajero Car Rental de cualquier
            discrepancia antes de salir de los locales de Viajero Car Rental.
        </p>

        <table class="sign-table">
            <tr>
                <th>Nombre del Cliente</th>
                <th>Firma del Cliente</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
            <tr>
                <td>
                    <input type="text" class="input-line" data-field="firma_cliente_nombre" placeholder="Nombre del cliente" value="{{ $clienteNombre ?? '' }}">
                </td>
                <td>
                    @if($contrato->firma_cliente)
                        <img src="{{ $contrato->firma_cliente }}" class="firma-img">
                    @else
                        <button class="btn-open-sign" data-type="cliente">Firmar Cliente</button>
                    @endif
                </td>
                <td>
                    <input type="text" class="input-line fecha-flatpickr" autocomplete="off" readonly data-field="firma_cliente_fecha">
                </td>
                <td>
                    <input type="time" class="input-line" data-field="firma_cliente_hora">
                </td>
            </tr>
        </table>

        <h3 class="sec-title">Sólo personal de Viajero</h3>

        <input type="hidden" id="firmaArrendadorSrc" value="{{ $contrato->firma_arrendador ?? '' }}">
        <input type="hidden" id="firmaRecibioSrc" value="{{ $contrato->firma_recibio ?? '' }}">

        <table class="sign-table">
            <tr>
                <th>Entregó</th>
                <th>Firma</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
            <tr>
                <td>
                    <input type="text" class="input-line" data-field="entrego_nombre" placeholder="Nombre del agente que entrega" value="{{ $asesorNombre ?? '' }}">
                </td>
                <td>
                    @if($contrato->firma_arrendador)
                        <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
                    @else
                        <button class="btn-open-sign" data-type="arrendador">Firmar Agente</button>
                    @endif
                </td>
                <td>
                    <input type="text" class="input-line fecha-flatpickr" autocomplete="off" readonly data-field="entrego_fecha">
                </td>
                <td>
                    <input type="time" class="input-line" data-field="entrego_hora">
                </td>
            </tr>

            <tr>
                <th>Recibió</th>
                <th>Firma</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
            <tr>
                <td>
                    @php $recibioNombre = trim($contrato->recibio_nombre ?? ''); @endphp
                    <select id="selectRecibioNombre" class="input-line" data-field="recibio_nombre">
                        <option value="">Selecciona agente...</option>
                        @foreach($agentes as $ag)
                            @php
                                $nombreAgente = trim($ag->nombre);
                                if ($recibioNombre !== '') {
                                    $seleccionado = ($nombreAgente === $recibioNombre) ? 'selected' : '';
                                } else {
                                    $seleccionado = '';
                                }
                            @endphp
                            <option value="{{ $nombreAgente }}" {{ $seleccionado }}>
                                {{ $nombreAgente }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    @php $firmaRecibio = $contrato->firma_recibio ?? null; @endphp
                    @if($firmaRecibio)
                        <img src="{{ $firmaRecibio }}" class="firma-img">
                    @else
                        <button class="btn-open-sign" data-type="recibio">Firmar quien recibe</button>
                    @endif
                </td>
                <td>
                    <input type="text" class="input-line fecha-flatpickr" autocomplete="off" readonly data-field="recibio_fecha">
                </td>
                <td>
                    <input type="time" class="input-line" data-field="recibio_hora">
                </td>
            </tr>
        </table>
    </section>

    {{-- ACCIONES --}}
    <section class="paper-section">
        <div class="checklist-actions">
            <button type="button" id="btnChecklistSalida" class="btn btn-primary" {{ $from === 'apartar' ? 'disabled' : ($modo === 'regreso' ? 'disabled' : '') }}>
                Enviar checklist de salida
            </button>

            <button type="button" id="btnChecklistEntrada" class="btn btn-outline-primary" {{ $from === 'apartar' ? 'disabled' : ($modo === 'salida' ? 'disabled' : '') }}>
                Enviar checklist de regreso
            </button>
        </div>
    </section>

    {{-- MODAL FIRMAS --}}
    <div id="modalFirma" class="modal-firma">
        <div class="modal-content">
            <h3 id="tituloModalFirma">Firma</h3>
            <canvas id="padFirma" width="400" height="180"></canvas>
            <div class="modal-buttons">
                <button id="btnClearFirma" class="btn-clear"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>Limpiar</button>
                <button id="btnGuardarFirma" class="btn-save"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Guardar</button>
                <button id="btnCerrarModal" class="btn-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>Cerrar</button>
            </div>
        </div>
    </div>

    <style>
    .modal-firma{
        position: fixed;
        inset: 0;
        background: rgba(17,24,39,.55);
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        padding: 16px;
    }

    .modal-content{
        position: relative;
        background: linear-gradient(180deg, #fffdfb 0%, #ffffff 55%);
        padding: 0;
        border-radius: 20px;
        width: 460px;
        max-width: calc(100vw - 24px);
        text-align: center;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(214,0,28,.12), 0 30px 80px rgba(15,23,42,.22);
        border: 1px solid rgba(214,0,28,.10);
        animation: modalFirmaIn .3s cubic-bezier(.2,.85,.25,1);
    }

    .modal-content::before{
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff4d63, var(--brand, #D6001C));
    }

    @keyframes modalFirmaIn{
        from{ opacity:0; transform: translateY(14px) scale(.96); }
        to{ opacity:1; transform: none; }
    }

    .modal-content h3{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        margin: 0;
        padding: 20px 22px 16px;
        font-size: 1.1rem;
        font-weight: 800;
        color: #1f2937;
        letter-spacing: -.2px;
        border-bottom: 1px solid #f0f1f4;
    }

    .modal-content h3::before{
        content: "";
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
        background: no-repeat center/contain url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23D6001C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 20h9'/%3E%3Cpath d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z'/%3E%3C/svg%3E");
    }

    #padFirma{
        display: block;
        margin: 20px auto 6px;
        border: 2px dashed #e6b8bf;
        border-radius: 14px;
        background-color: #fcfcfd;
        background-image: linear-gradient(to right, transparent 7%, #e6e9ee 7%, #e6e9ee 93%, transparent 93%);
        background-size: 100% 1px;
        background-position: 0 76%;
        background-repeat: no-repeat;
        box-shadow: inset 0 2px 10px rgba(15,23,42,.05);
        touch-action: none;
        cursor: crosshair;
    }

    .modal-buttons{
        display: flex;
        justify-content: center;
        gap: 10px;
        padding: 6px 22px 22px;
        flex-wrap: wrap;
    }

    .modal-buttons button{
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border: none;
        border-radius: 999px;
        cursor: pointer;
        font-weight: 700;
        font-size: .9rem;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease, filter .15s ease;
    }

    .modal-buttons button svg{ width: 16px; height: 16px; }

    .modal-buttons button:hover{ transform: translateY(-2px); }
    .modal-buttons button:active{ transform: translateY(0); }

    .btn-clear{ background:#eef1f5; color:#374151; }
    .btn-clear:hover{ background:#e5e9ef; }
    .btn-save{ background:#16a34a; color:#fff; box-shadow: 0 6px 16px rgba(22,163,74,.28); }
    .btn-save:hover{ box-shadow: 0 10px 22px rgba(22,163,74,.34); filter: brightness(1.02); }
    .btn-close{ background: var(--brand, #D6001C); color:#fff; box-shadow: 0 6px 16px rgba(214,0,28,.26); }
    .btn-close:hover{ box-shadow: 0 10px 22px rgba(214,0,28,.32); filter: brightness(1.03); }

    /* ===== OVERLAY CONFIRMACIÓN DE ENVÍO (checklist) ===== */
    .chk-overlay{
        position: fixed;
        inset: 0;
        z-index: 100000000;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(17,24,39,.5);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .25s ease, visibility .25s ease;
    }
    .chk-overlay.show{ opacity:1; visibility:visible; pointer-events:auto; }
    .chk-card{
        position: relative;
        background: linear-gradient(180deg, #fffdfb 0%, #ffffff 60%);
        border-radius: 24px;
        padding: 38px 46px 34px;
        min-width: 340px;
        max-width: 90vw;
        text-align: center;
        box-shadow: 0 18px 40px rgba(214,0,28,.12), 0 30px 80px rgba(15,23,42,.22);
        border: 1px solid rgba(214,0,28,.10);
        overflow: hidden;
        transform: translateY(14px) scale(.95);
        transition: transform .38s cubic-bezier(.2,.85,.25,1);
    }
    .chk-card::before{
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff4d63, var(--brand, #D6001C));
    }
    .chk-overlay.show .chk-card{ transform: none; }
    .chk-loader, .chk-success{ display:none; }
    .chk-overlay.is-loading .chk-loader{ display:block; }
    .chk-overlay.is-success .chk-success{ display:block; }
    .chk-car-stage{
        position: relative;
        width: 240px;
        max-width: 100%;
        height: 72px;
        margin: 4px auto 12px;
        overflow: hidden;
    }
    .chk-car{
        position: absolute;
        top: 8px;
        left: 0;
        width: 58px;
        height: 58px;
        color: var(--brand, #D6001C);
        animation: chkDrive 2.2s ease-in-out infinite;
    }
    .chk-car svg{
        width: 100%;
        height: 100%;
        filter: drop-shadow(0 6px 6px rgba(0,0,0,.12));
    }
    @keyframes chkDrive{
        0%   { left: -70px; transform: translateY(0) rotate(0deg); }
        8%   { transform: translateY(0) rotate(-2deg); }
        50%  { left: 46%; transform: translateY(-4px) rotate(1deg); }
        70%  { transform: translateY(0) rotate(-1deg); }
        100% { left: 240px; transform: translateY(0) rotate(0deg); }
    }
    .chk-msg{ margin:0; font-weight:700; font-size:1rem; color:#1f2937; }
    .chk-icon-thumb{
        width: 74px;
        height: 74px;
        margin: 4px auto 14px;
        color: var(--brand, #D6001C);
        animation: chkThumbPop .6s cubic-bezier(.18,1.3,.4,1) both;
        transform-origin: 60% 80%;
    }
    .chk-icon-thumb svg{ width:100%; height:100%; }
    @keyframes chkThumbPop{
        0%{ transform: scale(0) rotate(-25deg); opacity:0; }
        55%{ transform: scale(1.2) rotate(8deg); opacity:1; }
        75%{ transform: scale(1) rotate(-6deg); }
        100%{ transform: scale(1) rotate(0); }
    }
    .chk-title{ margin:0 0 4px; font-size:1.15rem; font-weight:800; color:#1f2937; letter-spacing:-.2px; }
    .chk-sub{ margin:0; font-size:.9rem; font-weight:600; color:#6b7280; }
    @media (max-width: 560px){
        .chk-card{ min-width:0; width:100%; padding:26px 20px; border-radius:18px; }
    }

    /* ===== RESPONSIVE BOTONES DE ENVÍO (solo móvil ≤600px) ===== */
    @media (max-width: 600px){
        .checklist-actions{
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        .checklist-actions #btnChecklistSalida{
            order: 1;   /* Salida arriba */
        }
        .checklist-actions #btnChecklistEntrada{
            order: 2;   /* Regreso abajo */
        }
        .checklist-actions .btn{
            width: 100%;
            white-space: normal;      /* que el texto no se corte, que baje de línea */
            text-align: center;
            box-sizing: border-box;
        }
    }

    /* ===== MODAL DE AVISO (éxito / error) ===== */
    .aviso-overlay{
        position: fixed;
        inset: 0;
        z-index: 100000002;
        display: none;
        align-items: flex-start;
        justify-content: center;
        background: rgba(17,24,39,.42);
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        padding: 16px;
    }
    .aviso-overlay.show{ display: flex; }
    .aviso-card{
        position: relative;
        background: #ffffff;
        border-radius: 18px;
        width: 400px;
        max-width: calc(100vw - 24px);
        padding: 26px 26px 24px;
        text-align: center;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15,23,42,.16), 0 30px 80px rgba(15,23,42,.22);
        border: 1px solid rgba(15,23,42,.06);
        animation: avisoIn .26s cubic-bezier(.2,.85,.25,1);
    }
    .aviso-card::before{
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: #9ca3af;
    }
    /* Variante ÉXITO (verde) */
    .aviso-card.is-success::before{
        background: linear-gradient(90deg, #34d399, #16a34a);
    }
    .aviso-card.is-success .aviso-icon{
        background: rgba(22,163,74,.10);
        color: #16a34a;
    }
    /* Variante ERROR (rojo de marca) */
    .aviso-card.is-error::before{
        background: linear-gradient(90deg, #ff4d63, var(--brand, #D6001C));
    }
    .aviso-card.is-error .aviso-icon{
        background: rgba(214,0,28,.10);
        color: var(--brand, #D6001C);
    }
    @keyframes avisoIn{
        from{ opacity:0; transform: translateY(12px) scale(.96); }
        to{ opacity:1; transform: none; }
    }
    .aviso-icon{
        width: 58px;
        height: 58px;
        border-radius: 50%;
        margin: 6px auto 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: avisoIconPop .5s cubic-bezier(.18,1.3,.4,1) both;
    }
    .aviso-icon svg{ width: 32px; height: 32px; }
    @keyframes avisoIconPop{
        0%{ transform: scale(0); opacity:0; }
        60%{ transform: scale(1.15); opacity:1; }
        100%{ transform: scale(1); }
    }
    .aviso-msg{
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.5;
        white-space: pre-line;
    }

    /* ===== MODAL DE CONFIRMACIÓN PROPIO (borrar foto) ===== */
    .confirm-del-overlay{
        position: fixed;
        inset: 0;
        z-index: 100000001;
        display: none;
        align-items: flex-start;
        justify-content: center;
        background: rgba(120,10,20,.45);
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
        padding: 16px;
    }
    .confirm-del-overlay.show{ display: flex; }
    .confirm-del-card{
        position: relative;
        background: linear-gradient(180deg, #fff5f6 0%, #ffffff 60%);
        border-radius: 20px;
        width: 420px;
        max-width: calc(100vw - 24px);
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(214,0,28,.20), 0 30px 80px rgba(120,10,20,.28);
        border: 1px solid rgba(214,0,28,.18);
        animation: confirmDelIn .28s cubic-bezier(.2,.85,.25,1);
    }
    .confirm-del-card::before{
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff4d63, var(--brand, #D6001C));
    }
    @keyframes confirmDelIn{
        from{ opacity:0; transform: translateY(14px) scale(.96); }
        to{ opacity:1; transform: none; }
    }
    .confirm-del-head{
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 22px 24px 14px;
    }
    .confirm-del-icon{
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: rgba(214,0,28,.10);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .confirm-del-icon svg{
        width: 26px;
        height: 26px;
        color: var(--brand, #D6001C);
    }
    .confirm-del-title{
        margin: 0;
        font-size: 1.12rem;
        font-weight: 800;
        color: #7a1520;
        letter-spacing: -.2px;
    }
    .confirm-del-body{
        padding: 0 24px 6px 82px;
        margin-top: -6px;
    }
    .confirm-del-text{
        margin: 0;
        color: #5b3a3e;
        font-size: .95rem;
        line-height: 1.55;
    }
    .confirm-del-preview{
        display: flex;
        justify-content: center;
        padding: 14px 24px 4px;
    }
    .confirm-del-preview img{
        max-width: 160px;
        max-height: 110px;
        border-radius: 10px;
        border: 2px solid rgba(214,0,28,.25);
        box-shadow: 0 4px 12px rgba(120,10,20,.18);
        object-fit: cover;
    }
    .confirm-del-actions{
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px 22px;
    }
    .confirm-del-actions button{
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: none;
        border-radius: 999px;
        padding: 10px 22px;
        font-weight: 700;
        font-size: .92rem;
        cursor: pointer;
        transition: transform .12s ease, filter .12s ease, box-shadow .12s ease, background .12s ease;
    }
    .confirm-del-actions button svg{ width: 16px; height: 16px; }
    .confirm-del-cancel{
        background: #f0ebec;
        color: #5b3a3e;
    }
    .confirm-del-cancel:hover{ background: #e6dfe0; transform: translateY(-1px); }
    .confirm-del-ok{
        background: var(--brand, #D6001C);
        color: #fff;
        box-shadow: 0 6px 16px rgba(214,0,28,.30);
    }
    .confirm-del-ok:hover{ filter: brightness(1.06); transform: translateY(-1px); box-shadow: 0 10px 22px rgba(214,0,28,.36); }
    .confirm-del-ok:disabled{ opacity:.6; cursor: default; transform:none; }
    @media (max-width: 480px){
        .confirm-del-body{ padding-left: 24px; }
        .confirm-del-actions{ flex-wrap: wrap; }
    }

    /* ===== BOTÓN × EN MINIATURAS (nuevas y guardadas) ===== */
    .photo-preview .thumb{
        position: relative;
    }
    .photo-preview .thumb .rm{
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border: none;
        border-radius: 999px;
        background: rgba(214,0,28,.92);
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,.25);
        transition: transform .12s ease, background .12s ease;
        z-index: 2;
    }
    .photo-preview .thumb .rm:hover{
        transform: scale(1.1);
        background: #b0001a;
    }
    </style>

    <!-- OVERLAY CONFIRMACIÓN DE ENVÍO (checklist) -->
    <div class="chk-overlay" id="chkOverlay" aria-hidden="true">
        <div class="chk-card" role="status" aria-live="polite">
            <div class="chk-loader" id="chkLoader">
                <div class="chk-car-stage">
                    <span class="chk-car">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 17H3v-5l2-5h11l3 5h1a1 1 0 0 1 1 1v4h-2" />
                            <path d="M9 17h6" />
                            <circle cx="7" cy="17" r="2" />
                            <circle cx="17" cy="17" r="2" />
                            <path d="M5 12h14" />
                        </svg>
                    </span>
                </div>
                <p class="chk-msg" id="chkMsg">Enviando checklist…</p>
            </div>
            <div class="chk-success" id="chkSuccess">
                <div class="chk-icon-thumb">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M7 10v12" />
                        <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" />
                    </svg>
                </div>
                <h4 class="chk-title" id="chkTitle">¡Checklist enviado!</h4>
                <p class="chk-sub" id="chkSub">El correo se envió correctamente</p>
            </div>
        </div>
    </div>
    <!-- MODAL DE CONFIRMACIÓN PROPIO (borrar foto guardada) -->
    <div class="confirm-del-overlay" id="confirmDelOverlay" aria-hidden="true">
        <div class="confirm-del-card" role="alertdialog" aria-labelledby="confirmDelTitle">
            <div class="confirm-del-head">
                <div class="confirm-del-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <line x1="10" y1="11" x2="10" y2="17"/>
                        <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                </div>
                <h3 class="confirm-del-title" id="confirmDelTitle">Eliminar foto</h3>
            </div>
            <div class="confirm-del-body">
                <p class="confirm-del-text">
                    ¿Seguro que deseas eliminar esta foto? Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="confirm-del-preview" id="confirmDelPreview"></div>
            <div class="confirm-del-actions">
                <button type="button" class="confirm-del-cancel" id="confirmDelCancel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                    Cancelar
                </button>
                <button type="button" class="confirm-del-ok" id="confirmDelOk">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
    <!-- MODAL DE AVISO REUTILIZABLE (éxito / error), posicionado cerca de la acción -->
    <div class="aviso-overlay" id="avisoOverlay" aria-hidden="true">
        <div class="aviso-card" id="avisoCard" role="status" aria-live="polite">
            <div class="aviso-icon" id="avisoIcon">
                <!-- El SVG se inyecta por JS según sea éxito o error -->
            </div>
            <p class="aviso-msg" id="avisoMsg"></p>
        </div>
    </div>


</div>
@endsection

@section('js-vistareservacionesAdmin')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const contratoApp = document.getElementById("idContrato");
    const CONTRATO_ID = contratoApp.value;

    const CHECKLIST_ID = {{ $id }};
    const maxLength = 283;

    /* ==============================
       REDIRECCIÓN SEGURA (iframe-aware)
       Si el checklist está embebido en un iframe (contrato-final),
       redirige la ventana superior en vez del propio iframe;
       así NO se anida contrato-final dentro de sí mismo.
    ============================== */
    function redirigirContratoFinal() {
        const dest = `/admin/contrato-final/${CHECKLIST_ID}`;
        try {
            if (window.top && window.top !== window.self) {
                window.top.location.href = dest;
                return;
            }
        } catch (e) {
            // Si por alguna razón no se puede acceder a window.top, cae al modo normal.
        }
        window.location.href = dest;
    }

    /* ==============================
       AVISO VISIBLE (helper)
    ============================== */
    const AvisoModal = (() => {
        const overlay = document.getElementById("avisoOverlay");
        const card    = document.getElementById("avisoCard");
        const iconBox = document.getElementById("avisoIcon");
        const msgBox  = document.getElementById("avisoMsg");
        let timer = null;

        const SVG_OK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
        const SVG_ERR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16.5" x2="12" y2="16.5"/></svg>';

        function mostrar(tipo, mensaje, btn) {
            if (!overlay || !card) {
                // Respaldo si no existe el modal
                alert(mensaje);
                return;
            }

            if (timer) { clearTimeout(timer); timer = null; }

            card.classList.remove("is-success", "is-error");
            card.classList.add(tipo === "success" ? "is-success" : "is-error");
            if (iconBox) iconBox.innerHTML = (tipo === "success") ? SVG_OK : SVG_ERR;
            if (msgBox)  msgBox.textContent = mensaje || "";

            overlay.classList.add("show");
            overlay.setAttribute("aria-hidden", "false");

            // Posiciona cerca del botón/acción (si se pasó), como el modal de borrar
            if (btn && window.posicionarModalCerca) {
                requestAnimationFrame(() => {
                    posicionarModalCerca(overlay, card, btn);
                });
            } else {
                // Sin botón de referencia: centrado vertical aproximado
                card.style.marginTop = "";
                card.style.marginBottom = "";
            }

            // Autocierre a los 2.5 segundos
            timer = setTimeout(ocultar, 2500);
        }

        function ocultar() {
            if (!overlay) return;
            overlay.classList.remove("show");
            overlay.setAttribute("aria-hidden", "true");
            if (card) { card.style.marginTop = ""; card.style.marginBottom = ""; }
            if (timer) { clearTimeout(timer); timer = null; }
        }

        // Cerrar al tocar fuera de la tarjeta
        if (overlay) {
            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) ocultar();
            });
        }

        return {
            exito: (msg, btn) => mostrar("success", msg, btn),
            error: (msg, btn) => mostrar("error", msg, btn),
        };
    })();

    // Helpers cómodos (reemplazan a las alertas flotantes de alertify)
    function avisoError(mensaje, btn) {
        AvisoModal.error(mensaje, btn);
    }
    function avisoExito(mensaje, btn) {
        AvisoModal.exito(mensaje, btn);
    }

    /* ==============================
       FECHAS Y HORAS
    ============================== */
    const camposFecha = ["firma_cliente_fecha", "entrego_fecha"];
    const camposHora = ["firma_cliente_hora", "entrego_hora"];

    function actualizarFechasHorasAhora() {
        const ahora = new Date();
        const yyyy = ahora.getFullYear();
        const mm   = String(ahora.getMonth() + 1).padStart(2, "0");
        const dd   = String(ahora.getDate()).padStart(2, "0");
        const hh   = String(ahora.getHours()).padStart(2, "0");
        const min  = String(ahora.getMinutes()).padStart(2, "0");
        const fechaStr = `${yyyy}-${mm}-${dd}`;
        const horaStr  = `${hh}:${min}`;

        camposFecha.forEach((field) => {
            const input = document.querySelector(`[data-field="${field}"]`);
            if (input) {
                if (input._flatpickr) {
                    input._flatpickr.setDate(fechaStr, false);
                } else {
                    input.value = fechaStr;
                }
            }
        });

        camposHora.forEach((field) => {
            const input = document.querySelector(`[data-field="${field}"]`);
            if (input) { input.value = horaStr; }
        });
    }

    actualizarFechasHorasAhora();
    setInterval(actualizarFechasHorasAhora, 30 * 1000);

    /* ==============================
       COMPRIMIR IMAGEN
    ============================== */
    const uploaderFiles = {};

    function compressImage(file, maxWidth = 1600, quality = 0.7) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                let width  = img.width;
                let height = img.height;

                if (width > maxWidth) {
                    const ratio = maxWidth / width;
                    width  = maxWidth;
                    height = height * ratio;
                }

                const canvas = document.createElement("canvas");
                canvas.width  = width;
                canvas.height = height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            return reject(new Error("No se pudo comprimir la imagen"));
                        }

                        const base    = file.name.replace(/\.[^.]+$/, "");
                        const newName = `${base}-cmp.jpg`;

                        const compressedFile = new File([blob], newName, {
                            type: "image/jpeg",
                            lastModified: Date.now(),
                        });

                        resolve(compressedFile);
                    },
                    "image/jpeg",
                    quality
                );
            };

            img.onerror = () => reject(new Error("No se pudo leer la imagen"));
            img.src = URL.createObjectURL(file);
        });
    }

    /* ==============================
       GAUGE
    ============================== */
    function setupGauge(selectId, arcId, needleId, txtId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const arc = document.getElementById(arcId);
        const needle = document.getElementById(needleId);
        const txt = document.getElementById(txtId);

        function updateGauge() {
            const option = select.selectedOptions[0];
            const pct = option && option.dataset.pct ? parseFloat(option.dataset.pct) : 0;
            const val = option ? (option.value || "—") : "—";

            if (txt) { txt.textContent = val; }

            if (arc) {
                const offset = maxLength - (maxLength * (pct / 100));
                arc.style.strokeDashoffset = offset;
            }

            if (needle) {
                const angle = -90 + (pct * 1.8);
                needle.style.transform = `rotate(${angle}deg)`;
            }
        }

        select.addEventListener("change", updateGauge);

        const inicial = select.dataset.inicial;
        if (inicial) {
            [...select.options].forEach(op => {
                if (op.value === inicial) op.selected = true;
            });
        }
        updateGauge();
    }

    setupGauge("selectGasSalida", "arcSalida", "needleSalida", "gasSalidaTxt");
    setupGauge("selectGasRecibe", "arcRecibe", "needleRecibe", "gasRecibeTxt");

    /* ==============================
       GUARDAR GASOLINA
    ============================== */
    const selectGasRecibeEl = document.getElementById("selectGasRecibe");
    if (selectGasRecibeEl) {
        selectGasRecibeEl.addEventListener("change", async (e) => {
            const nivel = e.target.value;
            const resp = await fetch(`/checklist/${CHECKLIST_ID}/guardar-gasolina`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ gasolina_regreso: nivel })
            });

            const data = await resp.json();
            if (data.ok) {
                console.log(data.msg || "Gasolina de regreso guardada.");
            } else {
                console.error(data.msg || "Error al guardar gasolina de regreso.");
            }
        });
    }

    /* ==============================
       POSICIONAR MODAL
    ============================== */
    function posicionarModalCerca(overlay, content, btn) {
        if (!overlay || !content || !btn) return;

        overlay.style.alignItems = "flex-start";
        overlay.style.justifyContent = "center";

        const btnRect = btn.getBoundingClientRect();
        const modalH = content.offsetHeight || 0;
        const vpH = window.innerHeight;
        const margen = 16;

        let top = btnRect.top + (btnRect.height / 2) - (modalH / 2);

        const topMax = vpH - modalH - margen;
        if (top > topMax) top = topMax;
        if (top < margen) top = margen;

        content.style.marginTop = `${top}px`;
        content.style.marginBottom = `${margen}px`;
    }
    window.posicionarModalCerca = posicionarModalCerca;

    /* ==============================
       MODAL FIRMAS
    ============================== */
    const modal = document.getElementById("modalFirma");
    const canvas = document.getElementById("padFirma");
    const btnClear = document.getElementById("btnClearFirma");
    const btnGuardar = document.getElementById("btnGuardarFirma");
    const btnCerrar = document.getElementById("btnCerrarModal");
    const tituloModal = document.getElementById("tituloModalFirma");

    let tipoFirma = null;
    let signaturePad = new SignaturePad(canvas);

    document.querySelectorAll(".btn-open-sign").forEach(btn => {
        btn.addEventListener("click", () => {
            tipoFirma = btn.dataset.type;

            if (tipoFirma === "cliente") {
                tituloModal.textContent = "Firma del Cliente";
            } else if (tipoFirma === "arrendador") {
                tituloModal.textContent = "Firma del Agente que entrega";
            } else if (tipoFirma === "recibio") {
                tituloModal.textContent = "Firma del Agente que recibe";
            } else {
                tituloModal.textContent = "Firma";
            }

            signaturePad.clear();
            modal.style.display = "flex";
            const contenidoFirma = modal.querySelector(".modal-content");
            posicionarModalCerca(modal, contenidoFirma, btn);
        });
    });

    btnClear.onclick = () => signaturePad.clear();
    btnCerrar.onclick = () => { modal.style.display = "none"; };

    btnGuardar.onclick = async () => {
        if (signaturePad.isEmpty()) {
            console.warn("La firma está vacía.");
            return;
        }

        const dataURL = signaturePad.toDataURL("image/png");
        let url = null;

        if (tipoFirma === "cliente") {
            url = "/contrato/firma-cliente";
        } else if (tipoFirma === "arrendador") {
            url = "/contrato/firma-arrendador";
        } else if (tipoFirma === "recibio") {
            url = "/contrato/firma-recibio";
        }

        if (!url) {
            console.error("Tipo de firma desconocido.");
            return;
        }

        const resp = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id_contrato: CONTRATO_ID, firma: dataURL })
        });

        if (!resp.ok) {
            console.error("Error al guardar la firma.");
            return;
        }

        console.log("Firma guardada correctamente.");
        modal.style.display = "none";
        location.reload();
    };

    /* ==============================
       GUARDAR CAMPOS
    ============================== */
    document.querySelectorAll("[data-field]").forEach(input => {
        input.addEventListener("change", async () => {
            await fetch("/contrato/guardar-dato", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id_contrato: CONTRATO_ID,
                    campo: input.dataset.field,
                    valor: input.value
                })
            });
        });
    });

    /* ==============================
       KM SALIDA
    ============================== */
    const kmSalidaText = document.getElementById("kmSalidaText");
    const kmSalidaInput = document.getElementById("kmSalidaInput");
    const btnGuardarKmSalida = document.getElementById("btnGuardarKmSalida");
    const FROM = "{{ $from }}";

    if (kmSalidaText && kmSalidaInput && btnGuardarKmSalida) {
        if (FROM === "apartar") {
            kmSalidaText.addEventListener("click", () => {
                kmSalidaText.style.display = "none";
                kmSalidaInput.style.display = "inline-block";
                btnGuardarKmSalida.style.display = "inline-block";
                kmSalidaInput.focus();
            });

            btnGuardarKmSalida.addEventListener("click", async () => {
                const km = kmSalidaInput.value;

                if (!km || km < 0) {
                    console.warn("Kilometraje inválido.");
                    return;
                }

                const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/actualizar-km-salida`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ km_salida: km })
                });

                if (!resp.ok) {
                    console.error("Error al guardar kilometraje de salida.");
                    return;
                }

                kmSalidaText.textContent = km;
                kmSalidaInput.style.display = "none";
                btnGuardarKmSalida.style.display = "none";
                kmSalidaText.style.display = "inline";
                console.log("Kilometraje de salida guardado.");
            });
        }
    }

    /* ==============================
       GASOLINA SALIDA
    ============================== */
    const selectGasSalidaEl = document.getElementById("selectGasSalida");
    if (selectGasSalidaEl) {
        selectGasSalidaEl.addEventListener("change", async (e) => {
            const FROM = "{{ $from }}";
            if (FROM !== "apartar") return;

            const nivel = e.target.value;

            const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/guardar-gasolina-salida`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ gasolina_salida: nivel })
            });

            const data = await resp.json();
            if (data.ok) {
                console.log("Gasolina de salida guardada.");
            } else {
                console.error("Error al guardar gasolina.");
            }
        });
    }

    /* ==============================
       FOTOS
    ============================== */
    document.querySelectorAll('.photo-uploader input[type="file"]').forEach((input) => {
        input.addEventListener("change", async (e) => {
            const contenedor = e.target.closest(".photo-uploader");
            if (!contenedor) return;

            const slotName = contenedor.getAttribute("data-name");
            if (!slotName) return;

            const maxFilesAttr = contenedor.getAttribute("data-max-files");
            const maxFiles = maxFilesAttr ? parseInt(maxFilesAttr, 10) : 99;

            const previewDiv = document.getElementById(`prev-${slotName}`);
            if (!previewDiv) return;

            if (!uploaderFiles[slotName]) {
                uploaderFiles[slotName] = [];
            }

            let newFiles = Array.from(e.target.files || []);
            if (!newFiles.length) return;

            if (maxFiles === 1) {
                uploaderFiles[slotName] = [];
            } else {
                const actuales = uploaderFiles[slotName].length;
                const disponibles = maxFiles - actuales;

                if (disponibles <= 0) {
                    console.warn(`Ya alcanzaste el límite de ${maxFiles} fotos en este apartado.`);
                    input.value = "";
                    return;
                }

                if (newFiles.length > disponibles) {
                    console.warn(`Solo se permiten ${maxFiles} fotos en este apartado. Se tomarán solo las primeras ${disponibles}.`);
                    newFiles = newFiles.slice(0, disponibles);
                }
            }

            const compressedList = [];
            for (const file of newFiles) {
                if (!file.type.startsWith("image/")) continue;

                try {
                    const compressed = await compressImage(file, 1600, 0.7);
                    compressedList.push(compressed);
                } catch (err) {
                    console.error("Error al comprimir imagen:", err);
                    compressedList.push(file);
                }
            }

            uploaderFiles[slotName] = uploaderFiles[slotName].concat(compressedList);

            const FROM = "{{ $from }}";
            const MODO = "{{ $modo }}";

            if (FROM === "apartar" && MODO === "salida") {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    const formData = new FormData();

                    const mapSlotsToFields = {
                        frenteSalida:        "frente_salida",
                        parabrisasSalida:    "parabrisas_salida",
                        ladoConductorSalida: "lado_conductor_salida",
                        ladoPasajeroSalida:  "lado_pasajero_salida",
                        atrasSalida:         "atras_salida",
                        interioresSalida:    "interiores_salida[]",
                    };

                    for (const [slot, field] of Object.entries(mapSlotsToFields)) {
                        const files = uploaderFiles[slot] || [];
                        if (!files.length) continue;

                        if (slot === "interioresSalida") {
                            files.forEach(f => formData.append(field, f));
                        } else {
                            const f = files[files.length - 1];
                            formData.append(field, f);
                        }
                    }

                    const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/guardar-fotos-salida?from=apartar`, {
                        method: "POST",
                        headers: { "X-CSRF-TOKEN": token },
                        body: formData
                    });

                    const data = await resp.json();

                    if (data.ok) {
                        console.log("Imágenes guardadas automáticamente.");
                    } else {
                        console.error(data.msg || "Error al guardar imágenes.");
                    }

                } catch (err) {
                    console.error(err);
                    console.error("Error al guardar imágenes automáticamente.");
                }
            }

            input.value = "";

            previewDiv.innerHTML = "";
            uploaderFiles[slotName].forEach((file) => {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const thumb = document.createElement("div");
                    thumb.classList.add("thumb");
                    thumb.innerHTML = `
                        <img src="${ev.target.result}" alt="Vista previa">
                        <button type="button" class="rm" title="Quitar">×</button>
                    `;

                    previewDiv.appendChild(thumb);
                    previewDiv.removeAttribute("data-has-server-file");

                    thumb.querySelector(".rm").addEventListener("click", () => {
                        uploaderFiles[slotName] = uploaderFiles[slotName].filter(
                            (f) => f !== file
                        );
                        thumb.remove();
                    });
                };
                reader.readAsDataURL(file);
            });
        });
    });

    /* ==============================
       ELIMINAR FOTOS GUARDADAS (× en miniaturas que vienen del servidor)
       Borrado permanente inmediato. Solo aplica a fotos de SALIDA ya guardadas.
    ============================== */
    // Ejecuta el borrado real de una foto guardada
    async function borrarFotoGuardada(fotoId, thumb, btn) {
        if (btn) btn.disabled = true;

        // Capturamos un elemento de referencia ESTABLE para posicionar el aviso,
        // ANTES de remover la miniatura. El botón × vive dentro del thumb, así que
        // si lo usáramos después del remove() ya no tendría posición (saldría arriba).
        // Usamos el contenedor de fotos más cercano, que no se elimina.
        const refPos = (thumb && (thumb.closest(".photo-slot") || thumb.closest(".photo-preview"))) || btn;

        try {
            const resp = await fetch(`/admin/checklist/foto/${fotoId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                }
            });

            const data = await resp.json();

            if (resp.ok && data.ok) {
                if (thumb) thumb.remove();
                avisoExito(data.msg || "Foto eliminada.", refPos);
            } else {
                if (btn) btn.disabled = false;
                avisoError(data.msg || "No se pudo eliminar la foto.", refPos);
            }
        } catch (err) {
            console.error(err);
            if (btn) btn.disabled = false;
            avisoError("Error de red al eliminar la foto.", refPos);
        }
    }

    document.querySelectorAll(".photo-preview").forEach(preview => {
        preview.addEventListener("click", (e) => {
            const btn = e.target.closest(".rm-server");
            if (!btn) return;

            const thumb = btn.closest(".thumb");
            if (!thumb) return;

            const fotoId = thumb.getAttribute("data-foto-id");
            if (!fotoId) return;

            abrirConfirmBorrado(fotoId, thumb, btn);
        });
    });

    /* Modal de confirmación propio (posicionado junto a la foto) */
    const confirmDelOverlay = document.getElementById("confirmDelOverlay");
    const confirmDelCard    = confirmDelOverlay ? confirmDelOverlay.querySelector(".confirm-del-card") : null;
    const confirmDelPreview = document.getElementById("confirmDelPreview");
    const confirmDelOk      = document.getElementById("confirmDelOk");
    const confirmDelCancel  = document.getElementById("confirmDelCancel");

    let confirmDelData = { fotoId: null, thumb: null, btn: null };

    function abrirConfirmBorrado(fotoId, thumb, btn) {
        if (!confirmDelOverlay) {
            // Respaldo sin modal
            if (confirm("¿Eliminar esta foto? Esta acción no se puede deshacer.")) {
                borrarFotoGuardada(fotoId, thumb, btn);
            }
            return;
        }

        confirmDelData = { fotoId, thumb, btn };

        // Miniatura de la foto que se va a borrar
        if (confirmDelPreview) {
            confirmDelPreview.innerHTML = "";
            const imgOriginal = thumb.querySelector("img");
            if (imgOriginal) {
                const clon = document.createElement("img");
                clon.src = imgOriginal.src;
                clon.alt = "Foto a eliminar";
                confirmDelPreview.appendChild(clon);
            }
        }

        if (confirmDelOk) confirmDelOk.disabled = false;

        confirmDelOverlay.classList.add("show");
        confirmDelOverlay.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";

        // Posiciona la tarjeta a la altura del botón × que se pulsó
        if (confirmDelCard && window.posicionarModalCerca) {
            requestAnimationFrame(() => {
                posicionarModalCerca(confirmDelOverlay, confirmDelCard, btn);
            });
        }
    }

    function cerrarConfirmBorrado() {
        if (!confirmDelOverlay) return;
        confirmDelOverlay.classList.remove("show");
        confirmDelOverlay.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        if (confirmDelCard) { confirmDelCard.style.marginTop = ""; confirmDelCard.style.marginBottom = ""; }
        confirmDelData = { fotoId: null, thumb: null, btn: null };
    }

    if (confirmDelCancel) confirmDelCancel.addEventListener("click", cerrarConfirmBorrado);

    if (confirmDelOverlay) {
        confirmDelOverlay.addEventListener("click", (e) => {
            if (e.target === confirmDelOverlay) cerrarConfirmBorrado();
        });
    }

    if (confirmDelOk) {
        confirmDelOk.addEventListener("click", async () => {
            const { fotoId, thumb, btn } = confirmDelData;
            if (!fotoId) return;
            confirmDelOk.disabled = true;
            await borrarFotoGuardada(fotoId, thumb, btn);
            cerrarConfirmBorrado();
        });
    }

    /* ==============================
       OVERLAY CONFIRMACIÓN (envío)
    ============================== */
    const ChecklistOverlay = (() => {
        const overlay = document.getElementById("chkOverlay");
        const card = overlay ? overlay.querySelector(".chk-card") : null;
        let triggerBtn = null;

        // Posiciona la tarjeta a la altura del botón que abrió el overlay
        function posicionar() {
            if (!overlay || !card || !triggerBtn) return;
            overlay.style.alignItems = "flex-start";
            overlay.style.justifyContent = "center";
            const r = triggerBtn.getBoundingClientRect();
            const cardH = card.offsetHeight || 0;
            const vpH = window.innerHeight;
            const margen = 16;
            let top = r.top + (r.height / 2) - (cardH / 2);
            const topMax = vpH - cardH - margen;
            if (top > topMax) top = topMax;
            if (top < margen) top = margen;
            card.style.marginTop = Math.round(top) + "px";
            card.style.marginBottom = margen + "px";
        }

        function mostrarCargando(msg, btn) {
            if (!overlay) return;
            if (btn) triggerBtn = btn;
            overlay.classList.remove("is-success");
            overlay.classList.add("show", "is-loading");
            overlay.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            const m = document.getElementById("chkMsg");
            if (m && msg) m.textContent = msg;
            requestAnimationFrame(posicionar);
        }

        function mostrarExito(titulo, sub) {
            if (!overlay) return;
            overlay.classList.remove("is-loading");
            overlay.classList.add("show", "is-success");
            overlay.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            const t = document.getElementById("chkTitle");
            const s = document.getElementById("chkSub");
            if (t && titulo) t.textContent = titulo;
            if (s && sub) s.textContent = sub;
            requestAnimationFrame(posicionar);
        }

        function ocultar() {
            if (!overlay) return;
            overlay.classList.remove("show", "is-loading", "is-success");
            overlay.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
            if (card) { card.style.marginTop = ""; card.style.marginBottom = ""; }
        }
        return { mostrarCargando, mostrarExito, ocultar };
    })();

    /* ==============================
       ENVIAR CHECKLIST SALIDA
    ============================== */
    const btnChecklistSalida = document.getElementById("btnChecklistSalida");
    if (btnChecklistSalida) {
        btnChecklistSalida.addEventListener("click", async () => {
            if (btnChecklistSalida.dataset.enviando === "1") return;
            btnChecklistSalida.dataset.enviando = "1";
            btnChecklistSalida.disabled = true;
            ChecklistOverlay.mostrarCargando("Enviando checklist de salida…", btnChecklistSalida);

            let exito = false;
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const formData = new FormData();
                formData.append("_token", token);
                formData.append("tipo", "salida");

                const comentario = document.querySelector('[data-field="comentario_cliente"]');
                const danos      = document.querySelector('[data-field="danos_interiores"]');

                const fcFecha = document.querySelector('[data-field="firma_cliente_fecha"]');
                const fcHora  = document.querySelector('[data-field="firma_cliente_hora"]');
                const eFecha  = document.querySelector('[data-field="entrego_fecha"]');
                const eHora   = document.querySelector('[data-field="entrego_hora"]');

                formData.append("comentario_cliente", comentario ? comentario.value : "");
                formData.append("danos_interiores",   danos      ? danos.value      : "");
                formData.append("firma_cliente_fecha", fcFecha ? fcFecha.value : "");
                formData.append("firma_cliente_hora",  fcHora  ? fcHora.value  : "");
                formData.append("entrego_fecha",       eFecha  ? eFecha.value  : "");
                formData.append("entrego_hora",        eHora   ? eHora.value   : "");

                // Origen del envío. En "apartar" las fotos YA se guardaron al
                // subirlas (guardado automático), así que NO las reenviamos aquí
                // (evita duplicados) y avisamos al backend con ya_guardadas=1.
                const FROM_ENVIO = "{{ $from }}";
                const yaGuardadas = (FROM_ENVIO === "apartar");

                if (yaGuardadas) {
                    formData.append("ya_guardadas", "1");
                }

                const mapSlotsToFields = {
                    frenteSalida:        "frente_salida",
                    parabrisasSalida:    "parabrisas_salida",
                    ladoConductorSalida: "lado_conductor_salida",
                    ladoPasajeroSalida:  "lado_pasajero_salida",
                    atrasSalida:         "atras_salida",
                    interioresSalida:    "interiores_salida[]",
                };

                const MAX_MB    = 2048;
                const MAX_BYTES = MAX_MB * 1024 * 1024;

                // Solo adjuntamos archivos si NO vienen ya guardados desde apartar.
                if (!yaGuardadas) {
                    for (const [slotName, fieldName] of Object.entries(mapSlotsToFields)) {
                        const files = uploaderFiles[slotName] || [];
                        if (!files.length) continue;

                        if (slotName === "interioresSalida") {
                            for (const file of files) {
                                if (file.size > MAX_BYTES) {
                                    throw new Error(`La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB. El máximo permitido es ${MAX_MB} MB.`);
                                }
                                formData.append(fieldName, file);
                            }
                        } else {
                            const file = files[files.length - 1];
                            if (file.size > MAX_BYTES) {
                                throw new Error(`La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB. El máximo permitido es ${MAX_MB} MB.`);
                            }
                            formData.append(fieldName, file);
                        }
                    }
                }

                const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/enviar-salida`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": token },
                    body: formData
                });

                const rawText = await resp.text();
                let data = null;

                try {
                    data = JSON.parse(rawText);
                } catch (e) {}

                if (!resp.ok || !data || data.ok === false) {
                    let msg = "Error al enviar el checklist de salida.";

                    if (data && data.errors) {
                        msg = Object.values(data.errors).flat().join("\n");
                    } else if (data && data.msg) {
                        msg = data.msg;
                    } else if (
                        resp.status === 413 ||
                        rawText.toLowerCase().includes("post_max_size") ||
                        rawText.toLowerCase().includes("upload_max_filesize")
                    ) {
                        msg = "Las fotos son demasiado pesadas para el servidor. Intenta con menos fotos o en menor resolución.";
                    } else {
                        msg = `Error ${resp.status}:\n` + (rawText || "(sin cuerpo de respuesta)");
                    }

                    throw new Error(msg);
                }

                console.log(data.msg || "Checklist de salida guardado correctamente.");
                exito = true;
                ChecklistOverlay.mostrarExito("¡Checklist enviado!", "El correo de salida se envió correctamente.");
                setTimeout(() => {
                    redirigirContratoFinal();
                }, 1400);
            } catch (err) {
                console.error(err);
                ChecklistOverlay.ocultar();

                let msg = "Error de red al enviar el checklist de salida.";
                if (err && typeof err.message === "string" && err.message.includes("failed to upload")) {
                    msg = "Una de las fotos no se pudo subir (suele ser por tamaño o conexión). Intenta con menos fotos o en menor resolución.";
                } else if (err && err.message) {
                    msg = err.message;
                }
                avisoError(msg, btnChecklistSalida);
            } finally {
                if (!exito) {
                    btnChecklistSalida.dataset.enviando = "";
                    btnChecklistSalida.disabled = false;
                }
            }
        });
    }

    /* ==============================
       ENVIAR CHECKLIST REGRESO
    ============================== */
    const btnChecklistEntrada = document.getElementById("btnChecklistEntrada");
    if (btnChecklistEntrada) {
        btnChecklistEntrada.addEventListener("click", async () => {
            if (btnChecklistEntrada.dataset.enviando === "1") return;
            btnChecklistEntrada.dataset.enviando = "1";
            btnChecklistEntrada.disabled = true;
            ChecklistOverlay.mostrarCargando("Enviando checklist de regreso…", btnChecklistEntrada);

            let exito = false;
            try {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const formData = new FormData();
                formData.append("_token", token);
                formData.append("tipo", "entrada");

                const comentario = document.querySelector('[data-field="comentario_cliente"]');
                const danos      = document.querySelector('[data-field="danos_interiores"]');

                const rFecha  = document.querySelector('[data-field="recibio_fecha"]');
                const rHora   = document.querySelector('[data-field="recibio_hora"]');

                formData.append("comentario_cliente", comentario ? comentario.value : "");
                formData.append("danos_interiores",   danos      ? danos.value      : "");
                formData.append("recibio_fecha",       rFecha  ? rFecha.value  : "");
                formData.append("recibio_hora",        rHora   ? rHora.value   : "");

                const mapSlotsToFieldsEntrada = {
                    frenteRegreso:        "frente_regreso",
                    parabrisasRegreso:    "parabrisas_regreso",
                    ladoConductorRegreso: "lado_conductor_regreso",
                    ladoPasajeroRegreso:  "lado_pasajero_regreso",
                    atrasRegreso:         "atras_regreso",
                    interioresRegreso:    "interiores_regreso[]",
                };

                const MAX_MB    = 2048;
                const MAX_BYTES = MAX_MB * 1024 * 1024;

                let totalFotosRegreso = 0;

                for (const [slotName, fieldName] of Object.entries(mapSlotsToFieldsEntrada)) {
                    const files = uploaderFiles[slotName] || [];
                    if (!files.length) continue;

                    if (slotName === "interioresRegreso") {
                        for (const file of files) {
                            if (file.size > MAX_BYTES) {
                                throw new Error(`La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB. El máximo permitido es ${MAX_MB} MB.`);
                            }
                            formData.append(fieldName, file);
                            totalFotosRegreso++;
                        }
                    } else {
                        const file = files[files.length - 1];
                        if (file.size > MAX_BYTES) {
                            throw new Error(`La foto "${file.name}" pesa ${(file.size / 1024 / 1024).toFixed(1)} MB. El máximo permitido es ${MAX_MB} MB.`);
                        }
                        formData.append(fieldName, file);
                        totalFotosRegreso++;
                    }
                }

                if (!totalFotosRegreso) {
                    throw new Error("Debes cargar al menos una foto del vehículo (regreso).");
                }

                const resp = await fetch(`/admin/checklist/${CHECKLIST_ID}/enviar-entrada`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": token },
                    body: formData
                });

                const rawText = await resp.text();
                let data = null;

                try {
                    data = JSON.parse(rawText);
                } catch (e) {}

                if (!resp.ok || !data || data.ok === false) {
                    let msg = "Error al enviar el checklist de regreso.";

                    if (data && data.errors) {
                        msg = Object.values(data.errors).flat().join("\n");
                    } else if (data && data.msg) {
                        msg = data.msg;
                    } else if (
                        resp.status === 413 ||
                        rawText.toLowerCase().includes("post_max_size") ||
                        rawText.toLowerCase().includes("upload_max_filesize")
                    ) {
                        msg = "Las fotos son demasiado pesadas para el servidor. Intenta con menos fotos o en menor resolución.";
                    } else {
                        msg = `Error ${resp.status}:\n` + (rawText || "(sin cuerpo de respuesta)");
                    }

                    throw new Error(msg);
                }

                console.log(data.msg || "Checklist de regreso guardado correctamente.");
                exito = true;
                ChecklistOverlay.mostrarExito("¡Checklist enviado!", "El correo de regreso se envió correctamente.");
                setTimeout(() => {
                    redirigirContratoFinal();
                }, 1400);
            } catch (err) {
                console.error(err);
                ChecklistOverlay.ocultar();

                let msg = "Error de red al enviar el checklist de regreso.";
                if (err && typeof err.message === "string" && err.message.includes("failed to upload")) {
                    msg = "Una de las fotos no se pudo subir (suele ser por tamaño o conexión). Intenta con menos fotos o en menor resolución.";
                } else if (err && err.message) {
                    msg = err.message;
                }
                avisoError(msg, btnChecklistEntrada);
            } finally {
                if (!exito) {
                    btnChecklistEntrada.dataset.enviando = "";
                    btnChecklistEntrada.disabled = false;
                }
            }
        });
    }

    /* ==============================
       REUTILIZAR FIRMA
    ============================== */
    const selectRecibio = document.getElementById("selectRecibioNombre");
    const inputEntrego  = document.querySelector('[data-field="entrego_nombre"]');
    const firmaArrInput = document.getElementById("firmaArrendadorSrc");
    const firmaRecInput = document.getElementById("firmaRecibioSrc");

    if (selectRecibio && inputEntrego && firmaArrInput) {
        const firmaArrSrc = firmaArrInput.value || "";
        const firmaRecSrcInicial = firmaRecInput ? (firmaRecInput.value || "") : "";

        async function guardarFirmaRecibio(firma) {
            const resp = await fetch("/contrato/firma-recibio", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id_contrato: CONTRATO_ID,
                    firma: firma
                })
            });
            return resp.ok;
        }

        async function manejarCambioRecibio() {
            const nombreEntrego = (inputEntrego.value || "").trim();
            const nombreRecibio = (selectRecibio.value || "").trim();

            if (!nombreRecibio) {
                await guardarFirmaRecibio("");
                return;
            }

            if (nombreEntrego && nombreRecibio === nombreEntrego && firmaArrSrc) {
                const ok = await guardarFirmaRecibio(firmaArrSrc);
                if (ok) {
                    console.log("Se reutilizó la firma del agente que entrega para 'Recibió'.");
                    location.reload();
                }
                return;
            }

            await guardarFirmaRecibio("");
            console.warn("Seleccionaste otro agente. Debe capturar una nueva firma para 'Recibió'.");
            location.reload();
        }

        selectRecibio.addEventListener("change", manejarCambioRecibio);

        (function autoSyncAlCargar() {
            const nombreEntrego = (inputEntrego.value || "").trim();
            const nombreRecibio = (selectRecibio.value || "").trim();
            const firmaRecActual = firmaRecSrcInicial;

            if (!firmaRecActual &&
                firmaArrSrc &&
                nombreEntrego &&
                nombreEntrego === nombreRecibio) {
                manejarCambioRecibio();
            }
        })();
    }

});
</script>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
    /* ==============================
       CHECKLIST CALENDARIOS
    ============================== */
    const FP_MESES_CK = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    function fpCrearSelectAniosCK(fp, desde, hasta) {
        const wrapper = fp.calendarContainer.querySelector(".numInputWrapper");
        if (!wrapper || fp.calendarContainer.querySelector(".fp-year-select")) return;

        const select = document.createElement("select");
        select.className = "fp-year-select";
        for (let a = hasta; a >= desde; a--) {
            const op = document.createElement("option");
            op.value = a;
            op.textContent = a;
            select.appendChild(op);
        }
        select.value = fp.currentYear;
        select.addEventListener("change", (e) => {
            e.stopPropagation();
            fp.changeYear(parseInt(e.target.value, 10));
        });
        wrapper.parentNode.insertBefore(select, wrapper);
        wrapper.remove();
    }

    function fpCerrarPanelMesesCK(fp) {
        const panel = fp.calendarContainer.querySelector(".fp-meses-panel");
        if (panel) panel.classList.remove("abierto");
    }

    function fpActualizarTriggerMesCK(fp) {
        const span = fp.calendarContainer.querySelector(".fp-mes-trigger span");
        if (span) span.textContent = FP_MESES_CK[fp.currentMonth];
    }

    function fpCrearPanelMesesCK(fp) {
        if (fp.calendarContainer.querySelector(".fp-meses-panel")) return;

        const panel = document.createElement("div");
        panel.className = "fp-meses-panel";

        FP_MESES_CK.forEach((nombre, i) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "fp-mes-btn";
            btn.dataset.mes = i;
            btn.textContent = nombre.substring(0, 3);
            btn.title = nombre;
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                fp.changeMonth(i, false);
                fpCerrarPanelMesesCK(fp);
            });
            panel.appendChild(btn);
        });

        fp.calendarContainer.appendChild(panel);

        const dd = fp.calendarContainer.querySelector(".flatpickr-monthDropdown-months");
        if (dd) {
            const trigger = document.createElement("button");
            trigger.type = "button";
            trigger.className = "fp-mes-trigger";
            trigger.innerHTML = '<span></span> <i>&#9662;</i>';
            dd.parentNode.insertBefore(trigger, dd);
            dd.style.display = "none";
            trigger.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (panel.classList.contains("abierto")) {
                    fpCerrarPanelMesesCK(fp);
                } else {
                    panel.querySelectorAll(".fp-mes-btn").forEach(b => {
                        b.classList.toggle("activo", parseInt(b.dataset.mes, 10) === fp.currentMonth);
                    });
                    panel.classList.add("abierto");
                }
            });
        }

        fpActualizarTriggerMesCK(fp);
    }

    function posicionarCalendarioJuntoCampoCK(cal, campo) {
    if (!cal) return;

    const calW = cal.offsetWidth || 820;
    const calH = cal.offsetHeight || 480;
    const vpW = window.innerWidth || document.documentElement.clientWidth;
    const vpH = window.innerHeight || document.documentElement.clientHeight;

    if (vpW <= 1024) {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft || 0;

        let refTop = 0, refLeft = 0, refH = 0, refW = 0;
        if (campo && typeof campo.getBoundingClientRect === "function") {
            const r = campo.getBoundingClientRect();
            refTop = r.top;
            refLeft = r.left;
            refH = r.height;
            refW = r.width;
        }

        let top = scrollTop + refTop + refH + 6;
        if (refTop + refH + calH + 12 > vpH) {
            const arriba = refTop - calH - 6;
            if (arriba >= 8) top = scrollTop + arriba;
        }
        let left = scrollLeft + refLeft + (refW / 2) - (calW / 2);
        if (left + calW > scrollLeft + vpW - 8) left = scrollLeft + vpW - calW - 8;
        if (left < scrollLeft + 8) left = scrollLeft + 8;

        cal.style.setProperty("position", "absolute", "important");
        cal.style.setProperty("top", Math.round(top) + "px", "important");
        cal.style.setProperty("left", Math.round(left) + "px", "important");
        cal.style.setProperty("right", "auto", "important");
        cal.style.setProperty("bottom", "auto", "important");
        cal.style.setProperty("transform", "none", "important");
        return;
    }
    let top = vpH - calH - 20;
    if (top < 8) top = 8;

    cal.style.setProperty("position", "fixed", "important");
    cal.style.setProperty("top", Math.round(top) + "px", "important");
    cal.style.setProperty("left", "50%", "important");
    cal.style.setProperty("right", "auto", "important");
    cal.style.setProperty("bottom", "auto", "important");
    cal.style.setProperty("transform", "translateX(-50%)", "important");
}

    function initFlatpickrChecklist() {
        if (typeof flatpickr === "undefined") {
            console.error("Flatpickr no está cargado");
            return;
        }

        const inputs = document.querySelectorAll(".fecha-flatpickr");
        const anio = new Date().getFullYear();

        inputs.forEach(input => {
            if (input._flatpickr) return;

            const opciones = {
                locale: (flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : "default",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-M-Y",
                allowInput: false,
                clickOpens: true,
                disableMobile: true,
                monthSelectorType: "dropdown",

                onReady: function (sel, str, fp) {
                    fp.calendarContainer.classList.add("fp-centrado");
                    fpCrearSelectAniosCK(fp, anio - 1, anio + 5);
                    fpCrearPanelMesesCK(fp);

                    [input, fp.altInput].filter(Boolean).forEach(el => {
                        el.setAttribute("readonly", "readonly");
                        el.setAttribute("inputmode", "none");
                        el.style.caretColor = "transparent";
                        el.addEventListener("focus", function (ev) {
                            ev.target.blur();
                            if (!fp.isOpen) fp.open();
                        });
                    });
                },

                onOpen: function (sel, str, fp) {
                    document.body.classList.add("fp-modal-abierto");
                    const cal = fp.calendarContainer;
                    fpCerrarPanelMesesCK(fp);
                    const y = cal.querySelector(".fp-year-select");
                    if (y) y.value = fp.currentYear;
                    fpActualizarTriggerMesCK(fp);
                    if (document.activeElement) document.activeElement.blur();

                    const campo = fp.altInput || fp.input;
                    requestAnimationFrame(() => {
                        posicionarCalendarioJuntoCampoCK(cal, campo);
                    });
                },

                onClose: function (sel, str, fp) {
                    document.body.classList.remove("fp-modal-abierto");
                    fpCerrarPanelMesesCK(fp);
                },

                onMonthChange: function (sel, str, fp) { fpActualizarTriggerMesCK(fp); },

                onYearChange: function (sel, str, fp) {
                    const y = fp.calendarContainer.querySelector(".fp-year-select");
                    if (y) y.value = fp.currentYear;
                    fpActualizarTriggerMesCK(fp);
                }
            };

            flatpickr(input, opciones);
        });
    }

    document.addEventListener("DOMContentLoaded", initFlatpickrChecklist);
</script>

@endsection
