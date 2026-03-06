@extends('layouts.Ventas')
@section('Titulo', 'Contrato - Operación')

@section('css-vistaContrato')
    <link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')
    <main class="main" id="contratoApp" data-id-contrato="{{ $contrato?->id_contrato ?? '' }}"
      data-numero="{{ $contrato?->numero_contrato ?? '' }}" data-id-reservacion="{{ $reservacion?->id_reservacion ?? '' }}">

    <h1 class="h1">Gestión de Contrato</h1>
    <p style="color:#666; margin-bottom:10px;">
        <b>No. Contrato:</b> {{ $contrato?->numero_contrato ?? 'En proceso...' }}
    </p>

        <div class="grid">

            <section class="steps">

                <article class="step active" data-step="4">
                    <header>
                        <div class="badge">4</div>
                        <h3>PASO 4 · Configuración final</h3>
                    </header>

                    <div class="body">
                        <section class="section">
                            <div class="head">Ajusta asignación y cargos opcionales</div>
                            <div class="cnt">

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
                                                <div><b>Entrega:</b> <span id="lblSedePick">{{ $reservacion->sucursal_retiro_nombre ?? '—' }}</span></div>
                                                <div><b>Devolución:</b> <span id="lblSedeDrop">{{ $reservacion->sucursal_entrega_nombre ?? '—' }}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">🚗</div> Cambio de vehículo
                                        </div>
                                        <button id="editVeh" class="btn" style="background:#fff;border:1px solid var(--stroke);">
                                            ✏️ Editar
                                        </button>
                                    </div>
                                    <div class="body">
                                        <div class="kvline">
                                            <div class="k">Unidad</div>
                                            <div>
                                                <select id="vehAssign" disabled>
                                                    @if ($vehiculo)
                                                        <option value="{{ $vehiculo->id_vehiculo }}">
                                                            {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->placa }})
                                                        </option>
                                                    @else
                                                        <option value="">No hay vehículo asignado</option>
                                                    @endif
                                                </select>
                                                <div class="help" id="vehInfo" style="margin-top:6px">
                                                    Unidad seleccionada en la reservación.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">⛽</div> Gasolina faltante
                                        </div>
                                    </div>
                                    <div class="body">
                                        <div class="cargo-item" data-tipo="litros-gasolina">
                                            <div class="head">
                                                <div class="hTitle">
                                                    <div class="hIcon">🛢️</div> Litros faltantes
                                                </div>
                                                <div class="switch" id="switchGasLit" data-idconcepto="5"></div>
                                            </div>
                                            <div class="body">
                                                <div id="gasLitrosInputs" style="display:none;margin-top:10px;">
                                                    <label>Precio por litro:</label>
                                                    <input type="number" min="0" step="0.01" id="gasPrecioL" class="form-control">
                                                    <label style="margin-top:10px;">Litros faltantes:</label>
                                                    <input type="number" min="0" step="1" id="gasCantL" class="form-control">
                                                    <div style="margin-top:10px;font-weight:bold;">
                                                        Total gasolina: <span id="gasTotalHTML">$0.00 MXN</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="head">
                                        <div class="hTitle">
                                            <div class="hIcon">📍</div> Dropoff
                                        </div>
                                    </div>
                                    <div class="body">
                                        <div class="note">Selecciona la ubicación donde el cliente devolverá el vehículo.</div>
                                        <div class="switch" id="switchDropoff" data-idconcepto="6"></div>

                                        <div id="dropoffFields" style="display:none;margin-top:15px;">
                                            <div class="form-group">
                                                <label>Seleccionar ubicación</label>
                                                <select id="dropUbicacion" class="form-control">
                                                    <option value="">Seleccione...</option>
                                                    @foreach ($ubicaciones as $u)
                                                        <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km }}">
                                                            {{ $u->estado }} - {{ $u->destino }} ({{ $u->km }} km)
                                                        </option>
                                                    @endforeach
                                                    <option value="0">Dirección personalizada (manual)</option>
                                                </select>
                                            </div>
                                            <div id="dropGroupDireccion" class="form-group" style="display:none;margin-top:10px;">
                                                <label>Dirección personalizada</label>
                                                <input type="text" id="dropDireccion" class="form-control" placeholder="Ej. Calle Las Flores 123">
                                            </div>
                                            <div id="dropGroupKm" class="form-group" style="display:none;margin-top:10px;">
                                                <label>Kilómetros personalizados</label>
                                                <input type="number" min="0" id="dropKm" class="form-control" placeholder="Ej. 25">
                                            </div>
                                            <div id="dropCostoKm" style="margin-top:10px;color:#666;font-size:13px;display:none;">
                                                Costo por km: <b><span id="dropCostoKmHTML">$0.00</span></b>
                                            </div>
                                            <div style="margin-top:15px;font-weight:bold;">
                                                Total Dropoff: <span id="dropTotal">$0.00 MXN</span>
                                            </div>
                                        </div>
                                        <input type="hidden" id="deliveryPrecioKm" value="{{ $costoKmCategoria ?? 0 }}">
                                    </div>
                                </div>

                                <div class="card">
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
                                                    if ($cargo->id_concepto == 5 || $cargo->id_concepto == 6) continue;
                                                    
                                                    $activo = in_array($cargo->id_concepto, $cargosActivos);
                                                @endphp

                                                <div class="card cargo-item {{ $activo ? 'active' : '' }}" 
                                                    data-id="{{ $cargo->id_concepto }}" 
                                                    data-nombre="{{ $cargo->nombre }}" 
                                                    data-monto="{{ $cargo->monto_base ?? 0 }}">
                                                    
                                                    <div class="head">
                                                        <div class="hTitle">
                                                            <div class="hIcon">🧾</div> 
                                                            <b>{{ $cargo->nombre }}</b>
                                                        </div>
                                                        <div class="switch {{ $activo ? 'on' : '' }}" data-id="{{ $cargo->id_concepto }}"></div>
                                                    </div>
                                                    
                                                    <div class="body">
                                                        @if ($cargo->descripcion)
                                                            <p style="font-size: 12px; color: #666; margin-bottom: 8px;">{{ $cargo->descripcion }}</p>
                                                        @endif
                                                        <div class="precio" style="font-weight: bold; color: var(--primary);">
                                                            ${{ number_format($cargo->monto_base, 2) }} {{ $cargo->moneda ?? 'MXN' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="totalBox" style="margin-top:18px;">
                                            <div class="kv">
                                                <div>Total cargos</div>
                                                <div class="total" id="total_cargos">
                                                    $0.00 MXN
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="acciones" style="margin-top:20px;">
                                    <a href="/admin/contrato/{{ $reservacion->id_reservacion }}" class="btn gray" 
                                        onclick="localStorage.setItem('contratoPasoActual_{{ $reservacion->id_reservacion }}', '3');">
                                        ← Volver a Seguros
                                    </a>
                                    <button class="btn primary" id="go5" type="button">Continuar →</button>
                                </div>

                            </div>
                        </section>
                    </div>
                </article>

                <article class="step" data-step="5">
                    <header>
                        <div class="badge">5</div>
                        <h3>PASO 5 · Documentación (modo simple)</h3>
                    </header>

                    <div class="body">
                        <form id="formDocumentacion" action="{{ route('contrato.guardarDocumentacion') }}"
                            method="POST" enctype="multipart/form-data"
                            data-adicionales="{{ count($conductoresExtras ?? []) }}" data-actual="0"
                            data-conductores='@json($conductoresExtras ?? [])'
                            data-titular="{{ $contrato->nombre_titular ?? 'Titular' }}">
                            @csrf

                            <input type="hidden" id="id_contrato" name="id_contrato" value="{{ $contrato?->id_contrato ?? '' }}">
                            <input type="hidden" id="id_conductor" name="id_conductor" value="">
                            <input type="hidden" id="conductor_index" name="conductor_index" value="0">
                            <input type="hidden" id="total_conductores" value="{{ count($conductoresExtras ?? []) }}">

                            <section class="section" id="bloque-documentacion">
                                <div class="head">
                                    <span id="tituloPersona">Documentación del Titular</span>
                                </div>
                                <div class="cnt">
                                    <div class="form-grid">
                                        <div class="input-row">
                                            <label>Tipo de Identificación</label>
                                            <select id="idTipo" name="tipo_identificacion" required>
                                                <option value="INE">Credencial para Votar (INE/IFE)</option>
                                                <option value="Pasaporte">Pasaporte</option>
                                                <option value="Cedula">Cédula Profesional</option>
                                            </select>
                                        </div>
                                        <div class="input-row">
                                            <label>Número de Identificación</label>
                                            <input id="idNumero" name="numero_identificacion" type="text" placeholder="XXXX-XXXX-XXXX" maxlength="18" required autocomplete="off">
                                        </div>
                                        <div class="input-row">
                                            <label for="nombre">Nombres</label>
                                            <input id="nombre" name="nombre" type="text" required autocomplete="off">
                                        </div>
                                        <div class="input-row">
                                            <label for="apellido_paterno">Apellido Paterno</label>
                                            <input id="apellido_paterno" name="apellido_paterno" type="text" required>
                                        </div>
                                        <div class="input-row">
                                            <label for="apellido_materno">Apellido Materno</label>
                                            <input id="apellido_materno" name="apellido_materno" type="text" required>
                                        </div>
                                        <div class="input-row">
                                            <label>Contacto de Emergencia</label>
                                            <input id="contactoEmergencia" name="contacto_emergencia" type="text" placeholder="Nombre y teléfono" autocomplete="off">
                                        </div>
                                        <div class="input-row">
                                            <label>Fecha de Nacimiento</label>
                                            <input id="idNacimiento" name="fecha_nacimiento" type="date" required>
                                        </div>
                                        <div class="input-row">
                                            <label>Fecha de Vencimiento del ID</label>
                                            <input id="idVence" name="fecha_vencimiento_id" type="date" required>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top:12px">
                                        <div>
                                            <label>Fotografía Identificación — Frente</label>
                                            <div class="uploader" data-name="idFrente">
                                                <div class="msg">Toca para cámara o galería (JPG/PNG)</div>
                                                <input name="idFrente" type="file" accept="image/jpeg,image/png" required>
                                            </div>
                                            <div class="preview" id="prev-idFrente"></div>
                                        </div>
                                        <div>
                                            <label>Fotografía Identificación — Reverso</label>
                                            <div class="uploader" data-name="idReverso">
                                                <div class="msg">Toca para cámara o galería (JPG/PNG)</div>
                                                <input name="idReverso" type="file" accept="image/jpeg,image/png" required>
                                            </div>
                                            <div class="preview" id="prev-idReverso"></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="section" style="margin-top:18px">
                                <div class="head">Licencia de Conducir</div>
                                <div class="cnt">
                                    <div class="form-grid">
                                        <div class="input-row">
                                            <label>Número de Licencia</label>
                                            <input id="licNumero" name="numero_licencia" type="text" placeholder="Ej. QRO-123456" required autocomplete="off">
                                        </div>
                                        <div class="input-row">
                                            <label>PAIS</label>
                                            <select id="licEmite" name="emite_licencia" required>
                                                <option value="">Selecciona…</option>
                                                <option>México</option>
                                                <option>U.S.A</option>
                                                <option>BRASIL</option>
                                                <option>COLOMBIA</option>
                                                <option>CANADA</option>
                                            </select>
                                        </div>
                                        <div class="input-row">
                                            <label>Fecha de Emisión</label>
                                            <input id="licEmision" name="fecha_emision_licencia" type="date" required>
                                        </div>
                                        <div class="input-row">
                                            <label>Fecha de Vencimiento de la Licencia</label>
                                            <input id="licVence" name="fecha_vencimiento_licencia" type="date" required>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top:12px">
                                        <div>
                                            <label>Licencia — Frente</label>
                                            <div class="uploader" data-name="licFrente">
                                                <div class="msg">Toca para cámara o galería (JPG/PNG)</div>
                                                <input name="licFrente" type="file" accept="image/jpeg,image/png" required>
                                            </div>
                                            <div class="preview" id="prev-licFrente"></div>
                                        </div>
                                        <div>
                                            <label>Licencia — Reverso</label>
                                            <div class="uploader" data-name="licReverso">
                                                <div class="msg">Toca para cámara o galería (JPG/PNG)</div>
                                                <input name="licReverso" type="file" accept="image/jpeg,image/png" required>
                                            </div>
                                            <div class="preview" id="prev-licReverso"></div>
                                        </div>
                                    </div>

                                    <div id="alertaLicencia" class="pill-warn" style="margin-top:8px; display:none;">
                                        ⚠️ Licencia vencida: por favor sube una licencia vigente para continuar.
                                    </div>
                                    <div id="confirmacionLicencia" class="pill-ok" style="margin-top:8px; display:none;">
                                        ✅ Licencia vigente verificada correctamente.
                                    </div>
                                </div>
                            </section>

                            <div class="acciones" style="margin-top:20px;">
                                <button class="btn gray" id="back4" type="button">← Atrás</button>
                                <button class="btn primary" id="btnContinuarDoc" type="submit">Guardar y Continuar →</button>
                                <button class="btn success" id="btnSaltarDoc" type="button" style="margin-left:8px;">Continuar sin volver a subir →</button>
                                <div class="small" style="margin-top:8px;">Se guarda automáticamente. Requisitos: fotos de frente y reverso de INE y Licencia.</div>
                            </div>
                        </form>
                    </div>
                </article>

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
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;gap:8px;flex-wrap:wrap">
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
                                            <th>#</th><th>Fecha</th><th>Tipo</th><th>Origen</th><th>Monto</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="payBody">
                                        <tr><td colspan="6" style="text-align:center;color:#667085">NO EXISTEN PAGOS REGISTRADOS</td></tr>
                                    </tbody>
                                </table>

                                <div class="right" style="margin-top:10px">
                                    <button id="btnAdd" class="btn primary">REGISTRAR PAGO</button>
                                </div>
                            </div>
                        </section>

                        <div class="acciones" style="margin-top:20px;">
                            <button class="btn gray" id="back5" type="button">← Atrás</button>
                            <form id="formFinalizar" action="{{ route('contrato.finalizar', $idReservacion) }}" method="POST">
                                @csrf
                                <button class="btn primary" id="btnFinalizar">Crear Contrato</button>
                            </form>
                        </div>
                    </div>
                </article>

            </section>

            <aside class="sticky">
                <div class="card resumen-card">
                    <div class="head">Resumen del Contrato</div>

                    <div class="cnt resumen-compacto" id="resumenCompacto">
                        <div id="vehiculo_info" class="vehiculo-mini-wrap">
                            <img id="resumenImgVeh" src="{{ asset('img/default-car.png') }}" alt="Vehículo" class="vehiculo-img">
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
                                <h4>Código de reservación</h4><p id="detCodigo">—</p>
                            </section>
                            <section class="res-block">
                                <h4>Datos del cliente</h4>
                                <p id="detCliente">—</p><p id="detTelefono">—</p><p id="detEmail">—</p>
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
                                <p>Salida: <span id="detFechaSalida">—</span> · <span id="detHoraSalida">—</span></p>
                                <p>Entrega: <span id="detFechaEntrega">—</span> · <span id="detHoraEntrega">—</span></p>
                                <p>Días totales: <span id="detDiasRenta">—</span></p>
                            </section>
                            <section class="res-block">
                                <h4>Paquetes de cobertura</h4>
                                <ul id="r_seguros_lista" class="det-lista"><li class="empty">—</li></ul>
                                <p>Total: <b id="r_seguros_total">—</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Adicionales</h4>
                                <ul id="r_servicios_lista" class="det-lista"><li class="empty">—</li></ul>
                                <p>Total: <b id="r_servicios_total">—</b></p>
                            </section>
                            <section class="res-block">
                                <h4>Servicios adicionales</h4>
                                <ul id="r_cargos_lista" class="det-lista"><li class="empty">—</li></ul>
                            </section>
                            <section class="res-block">
                                <h4>Total desglosado</h4>
                                <p>Tarifa base: <b id="r_base_precio">—</b> <button id="btnEditarTarifa" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">✏️</button></p>
                                <p>Horas de cortesía: <span id="r_cortesia">1</span> <button id="btnEditarCortesia" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">✏️</button></p>
                                <div id="editorCortesia" style="display:none; margin-top:6px;">
                                    <select id="inputCortesia" style="padding:4px;border-radius:6px;border:1px solid #ccc;">
                                        <option value="1">1 hora</option>
                                        <option value="2">2 horas</option>
                                        <option value="3">3 horas</option>
                                    </select>
                                    <button id="btnGuardarCortesia" style="margin-left:8px;background:#2563eb;color:white;border:none;padding:4px 8px;border-radius:6px;cursor:pointer;">Guardar</button>
                                    <button id="btnCancelarCortesia" style="margin-left:4px;background:#ccc;border:none;padding:4px 8px;border-radius:6px;cursor:pointer;">Cancelar</button>
                                </div>
                                <p>Subtotal: <b id="r_subtotal">—</b></p>
                                <p>IVA: <b id="r_iva">—</b></p>
                                <p>Total contrato: <b id="r_total_final">—</b></p>
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

        </div> <div class="modal-back" id="mb">
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
                            <div class="paypal-box"><div id="paypal-button-container-modal"></div></div>
                        </div>
                        <div data-pane="tarjeta" style="display:none;">
                            <div class="method-grid">
                                <label class="mcard"><input type="radio" name="m" value="VISA"><img src="../assets/media/visa.png" alt=""><div><div class="ttl">VISA</div><div class="sub">Terminal</div></div></label>
                                <label class="mcard"><input type="radio" name="m" value="MASTERCARD"><img src="../assets/media/master.jpg" alt=""><div><div class="ttl">Mastercard</div><div class="sub">Terminal</div></div></label>
                                <label class="mcard"><input type="radio" name="m" value="AMEX"><img src="../assets/media/amex.png" alt=""><div><div class="ttl">AMEX</div><div class="sub">Terminal</div></div></label>
                                <label class="mcard"><input type="radio" name="m" value="DEBITO"><img src="../assets/media/debito.png" alt=""><div><div class="ttl">Débito</div><div class="sub">Terminal</div></div></label>
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
                                <label class="mcard"><input type="radio" name="m" value="TRANSFERENCIA"><img src="../assets/media/transfe.jpg" alt=""><div><div class="ttl">Transferencia</div></div></label>
                                <label class="mcard"><input type="radio" name="m" value="SPEI"><img src="../assets/media/spei.png" alt=""><div><div class="ttl">SPEI</div></div></label>
                                <label class="mcard"><input type="radio" name="m" value="DEPOSITO"><img src="../assets/media/deposito.png" alt=""><div><div class="ttl">Depósito</div></div></label>
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

        <div id="modalUpgrade" class="upgrade-modal">
            <div class="upgrade-card">
                <button class="upgrade-close" id="cerrarUpgrade">✕</button>
                <div class="upgrade-discount-badge"><span id="upgDescuento"></span></div>
                <div class="upgrade-image-wrapper"><img id="upgImagenVehiculo" src="" alt="Vehículo upgrade"></div>
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

    </main>
@endsection

@section('js-vistaContrato')
    <script>
        window.contratoId = {{ $contrato->id_contrato ?? 'null' }};
        window.clienteContratoUrl = "{{ route('contrato.obtenerCliente', $contrato->id_contrato ?? 0) }}";
    </script>
    <script src="{{ asset('js/ContratoGlobal.js') }}" defer></script>
    <script src="{{ asset('js/Contrato2.js') }}" defer></script>   
@endsection