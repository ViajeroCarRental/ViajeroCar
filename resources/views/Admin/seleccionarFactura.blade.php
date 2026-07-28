@extends('layouts.Ventas')

@section('Titulo', 'Facturación')

@section('css-vistaSelecionarFactura')
    <link rel="stylesheet" href="{{ asset('css/selecionarFactura.css') }}">
@endsection

@section('contenidoFacturar')
    <div class="facturacion-container">
        <h2 class="page-title">Facturación</h2>

        {{-- Mensajes --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Pestañas --}}
        <div class="tabs-container">
            <button onclick="mostrarTab('reservaciones')" id="tab-reservaciones" class="tab-btn active">Reservaciones</button>
            <button onclick="mostrarTab('contratos')" id="tab-contratos" class="tab-btn">Contratos Abiertos</button>
            <button onclick="mostrarTab('facturadas')" id="tab-facturadas" class="tab-btn">Facturadas</button>
            <button onclick="mostrarTab('canceladas')" id="tab-canceladas" class="tab-btn">Canceladas</button>
        </div>

        {{-- RESERVACIONES --}}
        <div id="tabla-reservaciones" class="tab-content">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-reservaciones"></tbody>
                </table>
            </div>
        </div>

        {{-- CONTRATOS --}}
        <div id="tabla-contratos" class="tab-content" style="display:none;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>No. Contrato</th>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-contratos"></tbody>
                </table>
            </div>
        </div>

        {{-- FACTURADAS --}}
        <div id="tabla-facturadas" class="tab-content" style="display:none;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>RFC</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-facturadas"></tbody>
                </table>
            </div>
        </div>

        {{-- CANCELADAS --}}
        <div id="tabla-canceladas" class="tab-content" style="display:none;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>RFC</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-canceladas"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==========================================
         MODAL DE FACTURAR
         ========================================== --}}
    <div id="modalFacturar" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-md">
            <button onclick="cerrarModal()" class="btn-close">&times;</button>

            <h3 class="modal-title">Generar Factura CFDI 4.0</h3>
            <p class="modal-desc">Completa los datos fiscales para emitir el comprobante.</p>

            <form action="{{ route('facturar.store') }}" method="POST">
                @csrf

                {{-- Datos del Cliente --}}
                <h4 class="section-title">Datos del Cliente (Receptor)</h4>

                <div class="form-group">
                    <label class="form-label">Nombre o Razón Social *</label>
                    <input type="text" name="nombre_razon_social" id="f_nombre" required class="form-control"
                        placeholder="Ej. Juan Pérez López">
                </div>

                <div class="form-group">
                    <label class="form-label">RFC *</label>
                    <input type="text" name="rfc" id="f_rfc" maxlength="13" required
                        class="form-control text-uppercase" placeholder="Ej. PELJ800101ABC">
                    <small id="f_rfc_hint" class="form-hint">13 caracteres = persona física · 12 = empresa</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Régimen Fiscal *</label>
                    <select name="regimen_fiscal" id="f_regimen" required class="form-control">
                        <option value="">Seleccione...</option>
                        <optgroup label="── Persona Física (RFC de 13 caracteres) ──">
                            <option value="605">605 – Sueldos y Salarios e Ingresos Asimilados a Salarios</option>
                            <option value="606">606 – Arrendamiento</option>
                            <option value="612">612 – Personas Físicas con Actividades Empresariales y Profesionales
                            </option>
                            <option value="626">626 – Régimen Simplificado de Confianza (RESICO)</option>
                            <!-- Agrega los demás regímenes aquí como los tenías -->
                        </optgroup>
                        <optgroup label="── Persona Moral / Empresa (RFC de 12 caracteres) ──">
                            <option value="601">601 – General de Ley Personas Morales</option>
                            <option value="603">603 – Personas Morales con Fines no Lucrativos</option>
                            <!-- Agrega los demás regímenes aquí -->
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Código Postal del Domicilio Fiscal *</label>
                    <input type="text" name="codigo_postal" maxlength="5" required class="form-control"
                        placeholder="Ej. 76000">
                </div>

                <div class="form-group">
                    <label class="form-label">Uso de CFDI *</label>
                    <select name="uso_cfdi" id="f_uso" required class="form-control">
                        <option value="">Primero elige tu régimen...</option>
                    </select>
                    <small id="f_uso_hint" class="form-hint">Para asalariados (605) usa S01. Para negocios (612) usa
                        G03.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo electrónico *</label>
                    <input type="email" name="correo" id="f_correo" required class="form-control"
                        placeholder="Ej. cliente@correo.com">
                </div>

                {{-- Datos de la Renta --}}
                <h4 class="section-title">Datos de la Renta</h4>

                <div class="form-group">
                    <label class="form-label">Folio de Reservación *</label>
                    <input type="text" name="folio_reservacion" id="f_folio" required class="form-control"
                        placeholder="Ej. R-2025-00123">
                </div>

                <div class="form-group">
                    <label class="form-label">Método de Pago *</label>
                    <select name="metodo_pago" required class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="PUE" selected>PUE – Pago en una sola exhibición</option>
                        <option value="PPD">PPD – Pago en parcialidades o diferido</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Forma de Pago *</label>
                    <select name="forma_pago" required class="form-control">
                        <option value="">Seleccione...</option>
                        <option value="01">Efectivo</option>
                        <option value="03">Transferencia electrónica</option>
                        <option value="04">Tarjeta de crédito</option>
                        <option value="28">Tarjeta de débito</option>
                        <option value="99">Por definir</option>
                    </select>
                </div>

                {{-- Concepto Facturable --}}
                <h4 class="section-title">Concepto Facturable (SAT)</h4>

                <div class="form-group">
                    <label class="form-label">Clave Producto o Servicio (SAT) *</label>
                    <input type="text" name="clave_producto" id="f_clave" value="78111808" required
                        class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Cantidad *</label>
                    <input type="number" name="cantidad" id="f_cantidad" step="0.01" min="1" value="1"
                        required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Unidad SAT *</label>
                    <input type="text" name="unidad_sat" value="E48" required class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción del Servicio *</label>
                    <textarea name="descripcion" id="f_descripcion" rows="2" required class="form-control"
                        placeholder="Ej. Renta de vehículo..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Valor Unitario * <small
                            style="font-weight:400; color:var(--text-muted);">(sin IVA)</small></label>
                    <input type="number" name="valor_unitario" id="f_valor" step="0.01" min="0" required
                        class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">IVA (16%)</label>
                    <input type="number" id="f_iva" step="0.01" readonly class="form-control"
                        placeholder="Se calcula automáticamente">
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Importe Total</label>
                    <input type="number" id="f_total" step="0.01" readonly class="form-control"
                        placeholder="Subtotal + IVA">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Generar Factura
                </button>
            </form>
        </div>
    </div>

    {{-- ==========================================
         MODAL DE CANCELACIÓN
         ========================================== --}}
    <div id="modalCancelar" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-sm">
            <button onclick="cerrarModalCancelar()" class="btn-close">&times;</button>

            <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Cancelar Factura</h3>
            <p class="modal-desc">Folio: <strong id="c_folio_texto"></strong></p>

            <div class="alert alert-warning" style="margin-bottom: 1.5rem; padding: 0.8rem;">
                La cancelación es definitiva. Elige el motivo correcto.
            </div>

            <form id="formCancelar" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Motivo de cancelación *</label>
                    <select name="motivo" id="c_motivo" required class="form-control">
                        <option value="">Seleccione el motivo...</option>
                        <option value="02">02 – Comprobante emitido con errores sin relación</option>
                        <option value="01">01 – Comprobante con errores CON relación (sustitución)</option>
                        <option value="03">03 – No se llevó a cabo la operación</option>
                        <option value="04">04 – Operación nominativa relacionada en factura global</option>
                    </select>
                </div>

                <div id="c_ayuda" class="form-hint"
                    style="background:#f9fafb; padding:0.75rem; border-radius:6px; display:none; margin-bottom:1rem;">
                </div>

                <div id="c_sustituto_wrap" class="form-group" style="display:none;">
                    <label class="form-label">Folio Fiscal (UUID) de la factura que sustituye *</label>
                    <input type="text" name="sustituto_id" id="c_sustituto" class="form-control"
                        placeholder="Ej. 88a49b93-e763-4ffe-9b3e...">
                    <small class="form-hint">Debes haber generado primero la factura nueva.</small>
                </div>

                <div class="btn-group">
                    <button type="button" onclick="cerrarModalCancelar()" class="btn btn-secondary">No cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar cancelación</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==========================================
         MODAL DE ENVIAR CORREO
         ========================================== --}}
    <div id="modalEnviar" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-sm">
            <button onclick="cerrarModalEnviar()" class="btn-close">&times;</button>

            <h3 class="modal-title"><i class="fas fa-envelope"></i> Enviar Factura</h3>
            <p class="modal-desc">Folio: <strong id="e_folio_texto"></strong></p>

            <form id="formEnviar" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Correo de destino</label>
                    <input type="email" name="correo_destino" id="e_correo" class="form-control"
                        placeholder="correo@ejemplo.com">
                    <small class="form-hint">Si lo dejas vacío, se envía al correo registrado en la factura.</small>
                </div>

                <div class="btn-group">
                    <button type="button" onclick="cerrarModalEnviar()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==========================================
         MODAL DE DETALLE CANCELADA
         ========================================== --}}
    <div id="modalDetalle" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-sm">
            <button onclick="cerrarModalDetalle()" class="btn-close">&times;</button>

            <h3 class="modal-title"><i class="fas fa-file-invoice"></i> Detalle de Factura</h3>

            <div style="margin-top: 1.5rem;">
                <div class="detail-row">
                    <strong>Folio:</strong> <span id="d_folio"></span>
                </div>
                <div class="detail-row">
                    <strong>Folio Fiscal (UUID):</strong> <span id="d_uuid" class="text-break"></span>
                </div>
                <div class="detail-row">
                    <strong>RFC:</strong> <span id="d_rfc"></span>
                </div>
                <div class="detail-row">
                    <strong>Cliente:</strong> <span id="d_nombre" class="text-break"></span>
                </div>
                <div class="detail-row">
                    <strong>Total:</strong> <span id="d_total"></span>
                </div>
                <div class="detail-row">
                    <strong>Fecha timbrado:</strong> <span id="d_fecha_timbrado"></span>
                </div>
                <div class="detail-row">
                    <strong>Origen:</strong> <span id="d_origen"></span>
                </div>
                <div class="detail-row">
                    <strong>ID Facturapi:</strong> <span id="d_facturapi_id" class="text-break"></span>
                </div>
            </div>

            <div class="alert alert-error" style="margin-top: 1.5rem; text-align: center;">
                <i class="fas fa-ban"></i> Esta factura está cancelada ante el SAT.
            </div>

            <button onclick="cerrarModalDetalle()" class="btn btn-primary" style="margin-top: 1rem;">Cerrar</button>
        </div>
    </div>
@endsection

@section('js-vistaFacturar')
    <script src="{{ asset('js/seleccionarFactura.js') }}"></script>
@endsection
