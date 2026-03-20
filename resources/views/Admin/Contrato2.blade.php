@extends('layouts.Ventas')
@section('Titulo', 'Contrato - Operación')

@section('css-vistaContrato')
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')

    @php
        use Carbon\Carbon;

        $idReservacion = $reservacion->id_reservacion;
        $idContratoReal = $contrato?->id_contrato;

        // fechas
        $fechaInicio = Carbon::parse($reservacion->fecha_inicio ?? now());
        $fechaFin = Carbon::parse($reservacion->fecha_fin ?? now()->addDay());
        $horaRetiro = Carbon::parse($reservacion->hora_retiro ?? '12:00:00');
        $horaEntrega = Carbon::parse($reservacion->hora_entrega ?? '12:00:00');
        $diasTotales = max(1, $fechaInicio->diffInDays($fechaFin));

        // Cargos

        // Dropoff (Concepto 6)
        $dropActivo = (bool) ($cargoDrop ?? false);
        $dropDetalle = $dropActivo && isset($cargoDrop->detalle) ? json_decode($cargoDrop->detalle) : null;
        $dropDest = $dropDetalle->destino ?? '';
        $dropKm = $dropDetalle->km ?? '';
        $dropTotal = $cargoDrop->monto ?? 0;
        $esManual = $dropActivo && $dropKm > 0 && !str_contains($dropDest, ' - ');

        // Gasolina (Concepto 5)
        $gasActivo = (bool) ($cargoGas ?? false);
        $gasDetalle = $gasActivo && isset($cargoGas->detalle) ? json_decode($cargoGas->detalle) : null;

        // Precios y Tarifas
        $categoriasCol = collect($categorias ?? []);
        $catActual = $categoriasCol->where('id_categoria', $reservacion->id_categoria ?? 0)->first();
        $precioBase = $catActual->precio_dia ?? ($catActual->precio ?? 0);

        $precioReal =
            $reservacion->tarifa_ajustada == 1 && $reservacion->tarifa_modificada > 0
                ? $reservacion->tarifa_modificada
                : $precioBase;

        $subtotal = $diasTotales * $precioReal;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        // Formato de Telefono
        $telOriginal = $reservacion->telefono_cliente ?? '';
        $soloNumeros = preg_replace('/[^0-9]/', '', $telOriginal);
        $telFinal =
            strlen($soloNumeros) == 10
                ? '(' . substr($soloNumeros, 0, 3) . ') ' . substr($soloNumeros, 3, 3) . '-' . substr($soloNumeros, 6)
                : ($telOriginal ?:
                '—');
    @endphp

    <main class="main" id="contratoApp" data-id-contrato="{{ $idContratoReal ?? '' }}"
        data-numero="{{ $contrato?->numero_contrato ?? '' }}" data-id-reservacion="{{ $idReservacion ?? '' }}"
        data-id-categoria="{{ $reservacion->id_categoria ?? '' }}">

        <h1 class="h1">Gestión de Contrato</h1>
        <p style="color:#666; margin-bottom:10px;">
            <b>No. Contrato:</b> {{ $contrato?->numero_contrato ?? 'En proceso...' }}
        </p>

        <div class="grid">

            <section class="steps">

                {{-- Paso 4 --}}
                <article class="step active" data-step="4">
                    <header>
                        <div class="badge">4</div>
                        <h3>PASO 4 · Configuración final</h3>
                    </header>

                    <div class="body">
                        <section class="section">
                            <div class="head">Ajusta asignación y cargos opcionales</div>
                            <div class="cnt">

                                {{-- Itinerario --}}
                                <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">🗓️</div> Itinerario programado
                                        </div>
                                    </div>
                                    <div class="body">
                                        <div class="note">
                                            <div class="ic">ℹ️</div>
                                            <div>
                                                <div><b>Entrega:</b> <span
                                                        id="lblSedePick">{{ $reservacion->sucursal_retiro_nombre ?? '—' }}</span>
                                                </div>
                                                <div><b>Devolución:</b> <span
                                                        id="lblSedeDrop">{{ $reservacion->sucursal_entrega_nombre ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">🚗</div> Vehículo asignado
                                        </div>
                                        <button id="editVeh" class="btn"
                                            style="background:#fff; border:1px solid var(--stroke); font-size:13px; padding:4px 10px;">
                                            ✏️ Cambiar unidad
                                        </button>
                                    </div>
                                    <div class="body">
                                        <div id="vehiculoAsignadoUI">
                                            @if (!empty($reservacion->id_vehiculo))
                                                <div
                                                    style="display:flex; align-items:center; gap:15px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;">
                                                    <div
                                                        style="font-size:24px; background:#fff; padding:8px; border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                                                        ✅</div>
                                                    <div>
                                                        <h4 style="margin:0; font-size:15px; color:#166534;">
                                                            {{ $reservacion->marca }} {{ $reservacion->modelo }}</h4>
                                                        <p style="margin:4px 0 0 0; font-size:12px; color:#166534;">
                                                            Placa: <b>{{ $reservacion->placa }}</b> | Color:
                                                            {{ $reservacion->color }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    style="padding:15px; background:#fef2f2; border:1px dashed #f87171; border-radius:8px; color:#b91c1c; text-align:center; font-size:13px;">
                                                    ⚠️ No hay unidad asignada. Por favor haz clic en "Cambiar unidad".
                                                </div>
                                            @endif
                                        </div>
                                        <select id="vehAssign" style="display:none;">
                                            @if (!empty($reservacion->id_vehiculo))
                                                <option value="{{ $reservacion->id_vehiculo }}" selected>
                                                    {{ $reservacion->placa }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                {{-- Gasolina Faltante --}}
                                {{-- <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">⛽</div> Gasolina faltante
                                        </div>
                                    </div>
                                    <div class="body">
                                        <div class="cargo-item" data-tipo="litros-gasolina" data-id="5">
                                            <div class="head">
                                                <div class="hTitle">
                                                    <div class="hIcon">🛢️</div> Litros faltantes
                                                </div>
                                                <div class="switch {{ $gasActivo ? 'on' : '' }}" id="switchGasLit"
                                                    data-idconcepto="5"></div>
                                            </div>
                                            <div class="body">
                                                <div id="gasLitrosInputs"
                                                    style="display:{{ $gasActivo ? 'block' : 'none' }}; margin-top:10px;">
                                                    <div class="form-grid">
                                                        <div>
                                                            <label>Precio por litro:</label>
                                                            <input type="number" min="0" step="0.01"
                                                                id="gasPrecioL" class="form-control"
                                                                value="{{ $gasDetalle->precio_litro ?? '' }}">
                                                        </div>
                                                        <div>
                                                            <label>Litros faltantes:</label>
                                                            <input type="number" min="0" step="1"
                                                                id="gasCantL" class="form-control"
                                                                value="{{ $gasDetalle->litros ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div
                                                        style="margin-top:10px; font-weight:bold; text-align:right; color:var(--primary);">
                                                        Total gasolina: <span
                                                            id="gasTotalHTML">${{ number_format($cargoGas->monto ?? 0, 2) }}
                                                            MXN</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}

                                {{-- Dropoff --}}
                                <div class="card cargo-item {{ $dropActivo ? 'active' : '' }}" data-id="6"
                                    data-monto="{{ $dropTotal }}">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">📍</div> Dropoff
                                        </div>
                                        <div class="switch {{ $dropActivo ? 'on' : '' }}" id="switchDropoff"
                                            data-idconcepto="6"></div>
                                    </div>
                                    <div class="body">
                                        <div class="note">Selecciona la ubicación donde el cliente devolverá el vehículo.
                                        </div>
                                        <div id="dropoffFields"
                                            style="display:{{ $dropActivo ? 'block' : 'none' }}; margin-top:15px;">
                                            <div class="form-group">
                                                <label>Seleccionar ubicación</label>
                                                <select id="dropUbicacion" class="form-control">
                                                    <option value="">Seleccione...</option>
                                                    @foreach ($ubicaciones as $u)
                                                        @php $nombreUbi = $u->estado . ' - ' . $u->destino; @endphp
                                                        <option value="{{ $u->id_ubicacion }}"
                                                            data-km="{{ $u->km }}"
                                                            {{ $dropDest == $nombreUbi ? 'selected' : '' }}>
                                                            {{ $nombreUbi }} ({{ $u->km }} km)
                                                        </option>
                                                    @endforeach
                                                    <option value="0" {{ $esManual ? 'selected' : '' }}>Dirección
                                                        personalizada (manual)</option>
                                                </select>
                                            </div>
                                            <div id="dropGroupDireccion" class="form-group"
                                                style="display:{{ $esManual ? 'block' : 'none' }}; margin-top:10px;">
                                                <label>Dirección personalizada</label>
                                                <input type="text" id="dropDireccion" class="form-control"
                                                    value="{{ $dropDest }}" placeholder="Ej. Calle Las Flores 123">
                                            </div>
                                            <div id="dropGroupKm" class="form-group"
                                                style="display:{{ $esManual ? 'block' : 'none' }}; margin-top:10px;">
                                                <label>Kilómetros personalizados</label>
                                                <input type="number" min="0" id="dropKm" class="form-control"
                                                    value="{{ $dropKm }}" placeholder="Ej. 25">
                                            </div>
                                            <div id="dropCostoKm" style="margin-top:10px; color:#666; font-size:13px;">
                                                Costo por km: <b><span
                                                        id="dropCostoKmHTML">${{ number_format($costoKmCategoria ?? 0, 2) }}</span></b>
                                            </div>
                                            <div
                                                style="margin-top:15px; font-weight:bold; text-align:right; color:var(--primary);">
                                                Total Dropoff: <span id="dropTotalHTML">${{ number_format($dropTotal, 2) }}
                                                    MXN</span>
                                            </div>
                                        </div>
                                        <input type="hidden" id="deliveryPrecioKm" value="{{ $costoKmCategoria ?? 0 }}">
                                    </div>
                                </div>

                                {{-- Otros Cargos Adicionales --}}
                                {{-- <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">💰</div> Otros cargos adicionales
                                        </div>
                                    </div>
                                    <div class="body">
                                        <div class="note">Activa solo los cargos que correspondan.</div>
                                        <div id="cargosGrid" class="add-grid">
                                            @foreach ($cargos_conceptos as $cargo)
                                                @php
                                                    $activo = in_array($cargo->id_concepto, $cargosActivos ?? []);
                                                @endphp
                                                <div class="card cargo-item {{ $activo ? 'active' : '' }}"
                                                    data-id="{{ $cargo->id_concepto }}"
                                                    data-monto="{{ $cargo->monto_base ?? 0 }}">
                                                    <div class="head">
                                                        <div class="hTitle">
                                                            <div class="hIcon">🧾</div>
                                                            <b>{{ $cargo->nombre }}</b>
                                                        </div>
                                                        <div class="switch {{ $activo ? 'on' : '' }}"
                                                            data-id="{{ $cargo->id_concepto }}"></div>
                                                    </div>
                                                    <div class="body">
                                                        <div class="precio"
                                                            style="font-weight: bold; color: var(--primary);">
                                                            ${{ number_format($cargo->monto_base, 2) }} MXN
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        
                                        <div class="totalBox"
                                            style="margin-top:18px; border-top: 2px solid #eee; padding-top: 15px;">
                                            <div class="kv">
                                                <div style="font-weight: bold; color:#d00;">Total cargos opcionales</div>
                                                <div class="total" id="total_cargos"
                                                    style="font-size: 22px; font-weight: bold; color:#d00;">
                                                    ${{ number_format($totalPaso4Server ?? 0, 2) }} MXN
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="acciones" style="margin-top:20px;">
                                    <a href="/admin/contrato/{{ $idReservacion }}" class="btn gray"
                                        onclick="localStorage.setItem('contratoPasoActual_{{ $idReservacion }}', '3');">
                                        ← Volver a Seguros
                                    </a>
                                    <button class="btn primary" id="go5" type="button">Continuar a Documentos
                                        →</button>
                                </div>

                            </div>
                        </section>
                    </div>
                </article>

                {{-- Paso 5 --}}
                <article class="step" data-step="5">
                    <header>
                        <div class="badge">5</div>
                        <h3>PASO 5 · Documentación de Conductores</h3>
                    </header>

                    <div class="body">
                        <form id="formDocumentacion" action="{{ route('contrato.guardarDocumentacion') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id_reservacion" value="{{ $idReservacion }}">
                            <input type="hidden" name="id_contrato" value="{{ $idContratoReal ?? '' }}">

                            {{-- TITULAR --}}
                            <div class="bloque-conductor-individual">
                                <section class="section">
                                    <div class="head">
                                        <span>Documentación del Titular: {{ $reservacion->nombre_cliente }}</span>
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
                                                    value="{{ $reservacion->nombre_cliente }}" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Apellido Paterno</label>
                                                <input name="conductores[0][apellido_paterno]" type="text" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Apellido Materno</label>
                                                <input name="conductores[0][apellido_materno]" type="text" required>
                                            </div>

                                            <div class="input-row">
                                                <label>Contacto de Emergencia</label>
                                                <input name="conductores[0][contacto_emergencia]" type="text"
                                                    maxlength="10" placeholder="Contacto de Emergencia" required>
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

                                        <div class="form-grid" style="margin-top:12px">
                                            <div>
                                                <label>Fotografía Identificación — Frente</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][idFrente]" type="file"
                                                        accept="image/*" required>
                                                </div>
                                                <div class="preview"></div>
                                            </div>

                                            <div>
                                                <label>Fotografía Identificación — Reverso</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][idReverso]" type="file"
                                                        accept="image/*" required>
                                                </div>
                                                <div class="preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                {{-- LICENCIA TITULAR --}}
                                <section class="section" style="margin-top:18px">
                                    {{-- <div class="head">Licencia de Conducir (Titular)</div> --}}

                                    <div class="head" style="display: flex; align-items: center; gap: 8px;">
                                        Licencia de Conducir (Titular)

                                        <button type="button" id="btnInfoLicencia" title="Aviso importante"
                                            style="background: transparent; border: none; cursor: pointer; font-size: 18px; color: #3b82f6; padding: 0; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;"
                                            onmouseover="this.style.transform='scale(1.1)'"
                                            onmouseout="this.style.transform='scale(1)'">
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

                                        <div class="form-grid" style="margin-top:12px">
                                            <div>
                                                <label>Licencia — Frente</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][licFrente]" type="file"
                                                        accept="image/*" required>
                                                </div>
                                                <div class="preview"></div>
                                            </div>

                                            <div>
                                                <label>Licencia — Reverso</label>
                                                <div class="uploader">
                                                    <input name="conductores[0][licReverso]" type="file"
                                                        accept="image/*" required>
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

                                <div class="bloque-conductor-individual"
                                    style="margin-top: 50px; border-top: 3px dashed #cbd5e1; padding-top: 30px;">

                                    <section class="section">
                                        <div class="head" style="background: #64748b;">
                                            <span>Documentación Conductor Adicional: {{ $extra['nombres'] }}</span>
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

                                            <div class="form-grid" style="margin-top:12px">
                                                <div>
                                                    <label>Identificación — Frente</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][idFrente]"
                                                            type="file" accept="image/*" required>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>

                                                <div>
                                                    <label>Identificación — Reverso</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][idReverso]"
                                                            type="file" accept="image/*" required>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- LICENCIA ADICIONAL --}}
                                    <section class="section" style="margin-top:18px">
                                        <div class="head" style="background: #94a3b8;">Licencia de Conducir (Adicional)
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

                                            <div class="form-grid" style="margin-top:12px">
                                                <div>
                                                    <label>Licencia — Frente</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][licFrente]"
                                                            type="file" accept="image/*" required>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>

                                                <div>
                                                    <label>Licencia — Reverso</label>
                                                    <div class="uploader">
                                                        <input name="conductores[{{ $idx }}][licReverso]"
                                                            type="file" accept="image/*" required>
                                                    </div>
                                                    <div class="preview"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            @endforeach

                            <div class="acciones"
                                style="margin-top:30px; padding: 20px; background: #f8fafc; border-radius: 8px;">
                                <button class="btn gray" id="back4" type="button">← Atrás</button>
                                <button class="btn primary" id="btnContinuarDoc" type="submit">
                                    Guardar Toda la Documentación →
                                </button>
                                <button class="btn success" id="btnSaltarDoc" type="button" style="margin-left:8px;">
                                    Continuar sin volver a subir →
                                </button>
                            </div>
                        </form>
                    </div>
                </article>

                {{-- Paso 6 --}}
                <article class="step" data-step="6">
                    <header>
                        <div class="badge">6</div>
                        <h3>PASO 6 · Estado de cuenta y pagos</h3>
                    </header>

                    <div class="body">
                        <section class="section">
                            <div class="head">Desglose de Pagos</div>
                            <div class="cnt">
                                <div class="row">
                                    <div>Tarifa Base (<span id="baseDescr">—</span>)</div>
                                    <div id="baseAmt">$0</div>
                                </div>
                                <div class="row">
                                    <div>Opciones de Renta</div>
                                    <div id="addsAmt">$0</div>
                                </div>
                                <div class="row">
                                    <div>Subtotal</div>
                                    <div id="ivaAmt">$0</div>
                                </div>
                                <div class="row">
                                    <div class="small">IVA (16%)</div>
                                    <div id="ivaOnly">$0</div>
                                </div>
                            </div>
                        </section>

                        <section class="section" style="margin-top:16px">
                            <div class="head">Estado de Cuenta</div>
                            <div class="cnt">
                                <div
                                    style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;flex-wrap:wrap">
                                    <div>
                                        <div class="small">Total del Contrato</div>
                                        <div class="total" id="totalContrato">$0</div>
                                    </div>
                                    <div>
                                        <div class="small">Saldo Pendiente</div>
                                        <div class="badge" id="saldoPendiente">$0</div>
                                    </div>
                                </div>

                                <h3 style="margin:6px 0 6px;font-size:14px">Pagos</h3>
                                <table class="table" id="tblPagos">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Origen</th>
                                            <th>Monto</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="payBody">
                                        <tr>
                                            <td colspan="6" style="text-align:center;color:#667085">NO EXISTEN PAGOS
                                                REGISTRADOS</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="right" style="margin-top:10px">
                                    <button id="btnAdd" class="btn primary">REGISTRAR PAGO</button>
                                </div>
                            </div>
                        </section>

                        <div class="acciones" style="margin-top:20px;">
                            <button class="btn gray" id="back5" type="button">← Atrás</button>
                            <form id="formFinalizar" action="{{ route('contrato.finalizar', $idReservacion) }}"
                                method="POST">
                                @csrf
                                <button class="btn primary" id="btnFinalizar">Crear Contrato</button>
                            </form>
                        </div>
                    </div>
                </article>

            </section>

            {{-- Resumen --}}
            <aside class="sticky">
                <div class="card resumen-card">
                    <div class="head">Resumen del Contrato</div>

                    <div class="cnt resumen-compacto" id="resumenCompacto">
                        <div id="vehiculo_info" class="vehiculo-mini-wrap">
                            <img id="resumenImgVeh" src="{{ asset('img/default-car.png') }}" loading="lazy"
                                alt="Vehículo" class="vehiculo-img">
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
                                <h4>Servicios adicionales</h4>
                                <ul id="r_cargos_lista" class="det-lista">
                                    <li class="empty">—</li>
                                </ul>
                                <p>Total: <b id="r_cargos_total">$0.00 MXN</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Total desglosado</h4>
                                <p>Tarifa base: <b id="r_base_precio">${{ number_format($precioReal, 2) }}</b>
                                    <button id="btnEditarTarifa"
                                        style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">✏️</button>
                                </p>
                                <p>Horas de cortesía: <b id="r_cortesia">{{ $reservacion->horas_cortesia ?? 1 }}</b>
                                    <button id="btnEditarCortesia"
                                        style="background:none; border:none; color:#2563eb; cursor:pointer; font-size:14px; margin-left:4px;"
                                        title="Editar cortesía">✏️</button>
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

        {{-- Modal Pagos --}}
        <div class="modal-back" id="mb">
            <div class="modal modal-pagos">
                <div class="head">
                    Registrar Pago
                    <button id="mx" class="btn gray" style="padding:6px 10px">✕</button>
                </div>
                <div class="body">
                    <div class="pay-groups" id="payTabs">
                        <button class="tab active" data-tab="paypal">PayPal</button>
                        <button class="tab" data-tab="tarjeta">Terminal</button>
                        <button class="tab" data-tab="efectivo">Efectivo</button>
                        <button class="tab" data-tab="transferencia">Transferencia / Depósito</button>
                    </div>
                    <div id="methods">
                        <div data-pane="paypal">
                            <p class="small">Al seleccionar PayPal, se abrirá la pasarela en línea.</p>
                            <div class="paypal-box">
                                <div id="paypal-button-container-modal"></div>
                            </div>
                        </div>
                        <div data-pane="tarjeta" style="display:none;">
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
                            <div style="margin-top:15px;">
                                <label>Foto del ticket (obligatorio)</label>
                                <input id="fileTerminal" type="file" accept="image/*,.pdf">
                            </div>
                        </div>
                        <div data-pane="efectivo" style="display:none;">
                            <p class="small">Se generará automáticamente un ticket interno.</p>
                        </div>
                        <div data-pane="transferencia" style="display:none;">
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
                            <div style="margin-top:15px;">
                                <label>Comprobante del pago (obligatorio)</label>
                                <input id="fileTransfer" type="file" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>

                    <fieldset style="margin-top:18px;">
                        <legend>Detalle del pago</legend>
                        <div class="form-grid">
                            <div>
                                <label>Tipo de Pago</label>
                                <select id="pTipo">
                                    <option value="PAGO RESERVACIÓN">PAGO RESERVACIÓN</option>
                                    <option value="ANTICIPO">ANTICIPO</option>
                                    <option value="DEPÓSITO">DEPÓSITO</option>
                                    <option value="LIQUIDACIÓN">LIQUIDACIÓN</option>
                                </select>
                            </div>
                            <div>
                                <label>Monto</label>
                                <input id="pMonto" type="number" step="0.01" min="0" placeholder="0.00">
                                <div class="err" id="pErr"></div>
                            </div>
                            <div style="grid-column:1/-1;">
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

        {{-- Modal Vehículos --}}
        <div id="modalVehiculos" class="modal-vehiculos">
            <div class="modal-content">
                <div class="modal-header">
                    <span>Vehículos disponibles</span>
                    <button type="button" id="cerrarModalVehiculos" class="close-btn">✕</button>
                </div>
                <div class="modal-select-categoria" style="margin: 15px 0;">
                    <label style="font-weight:600; font-size:14px;">Filtrar por categoría</label>
                    <select id="selectCategoriaModal" class="filtro-input" style="width: 100%; min-width: 200px;">
                        <option value="">Selecciona categoría...</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id_categoria }}"
                                {{ ($reservacion->id_categoria ?? 0) == $cat->id_categoria ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
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

    </main>
@endsection

@section('js-vistaContrato')
    <script>
        window.ID_RESERVACION = "{{ $idReservacion }}";
        window.ID_CONTRATO = "{{ $idContratoReal ?? '' }}";
        window.csrfToken = "{{ csrf_token() }}";

        window.ID_SERVICIO_MENOR = {{ $idServicioMenor ?? 0 }};

        window.contratoId = window.ID_CONTRATO;
        window.clienteContratoUrl = "{{ route('contrato.obtenerCliente', $idContratoReal ?? 0) }}";

        localStorage.setItem(`contratoPasoActual_${window.ID_RESERVACION}`, '4');
    </script>

    <script src="{{ asset('js/ContratoGlobal.js') }}" defer></script>
    <script src="{{ asset('js/Contrato2.js') }}" defer></script>
@endsection
