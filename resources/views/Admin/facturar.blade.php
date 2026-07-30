@extends('layouts.Ventas')

@section('Titulo', 'Facturar - Viajero Car')

{{-- CSS de la vista --}}
@section('css-vistaFacturar')
    <link rel="stylesheet" href="{{ asset('css/facturar.css') }}">
@endsection

@section('contenidoFacturar')

    <div class="wrap-facturar">
        <div class="factura-card">
            <div class="factura-header">
                <i class="fas fa-file-invoice-dollar"></i>
                <h2>Generar Factura CFDI 4.0</h2>
                <p>Completa los datos fiscales para emitir tu comprobante electrónico</p>
            </div>

            {{-- Mensajes de éxito/error --}}
            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            {{-- Botones de descarga pdf, xml y envio correo --}}
            @if (session('success') && session('factura_id'))
                <div class="factura-acciones" style="margin:1rem 0; display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ route('facturas.pdf', session('factura_id')) }}" class="btn-accion">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    <a href="{{ route('facturas.xml', session('factura_id')) }}" class="btn-accion">
                        <i class="fas fa-file-code"></i> Descargar XML
                    </a>
                    <form action="{{ route('facturas.enviar', session('factura_id')) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-accion">
                            <i class="fas fa-envelope"></i> Enviar por correo
                        </button>
                    </form>
                </div>
            @endif

            {{-- Errores de validación --}}
            @if ($errors->any())
                <div class="alert error">
                    <ul style="margin:0; padding-left:1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('facturar.store') }}" method="POST" class="factura-form">
                @csrf

                {{-- ================== DATOS DEL CLIENTE ================== --}}
                <div class="form-section">
                    <h3>Datos del Cliente (Receptor)</h3>

                    <div class="input-group">
                        <label for="nombre_razon_social">Nombre o Razón Social *</label>
                        <input type="text" id="nombre_razon_social" name="nombre_razon_social" required
                            value="{{ old('nombre_razon_social') }}" placeholder="Ej. Juan Pérez López">
                    </div>

                    <div class="input-group">
                        <label for="rfc">RFC *</label>
                        <input type="text" id="rfc" name="rfc" maxlength="13" required
                            value="{{ old('rfc') }}" placeholder="Ej. PELJ800101ABC" style="text-transform:uppercase;">
                    </div>

                    <div class="input-group">
                        <label for="regimen_fiscal">Régimen Fiscal *</label>
                        <select id="regimen_fiscal" name="regimen_fiscal" required>
                            <option value="">Seleccione...</option>
                            <optgroup label="── Persona Física (RFC de 13 caracteres) ──">
                                <option value="605" @selected(old('regimen_fiscal') == '605')>605 – Sueldos y Salarios e Ingresos
                                    Asimilados a Salarios</option>
                                <option value="606" @selected(old('regimen_fiscal') == '606')>606 – Arrendamiento</option>
                                <option value="607" @selected(old('regimen_fiscal') == '607')>607 – Régimen de Enajenación o
                                    Adquisición de Bienes</option>
                                <option value="608" @selected(old('regimen_fiscal') == '608')>608 – Demás ingresos</option>
                                <option value="610" @selected(old('regimen_fiscal') == '610')>610 – Residentes en el Extranjero sin
                                    Establecimiento Permanente en México</option>
                                <option value="611" @selected(old('regimen_fiscal') == '611')>611 – Ingresos por Dividendos (socios y
                                    accionistas)</option>
                                <option value="612" @selected(old('regimen_fiscal') == '612')>612 – Personas Físicas con Actividades
                                    Empresariales y Profesionales</option>
                                <option value="614" @selected(old('regimen_fiscal') == '614')>614 – Ingresos por intereses</option>
                                <option value="615" @selected(old('regimen_fiscal') == '615')>615 – Régimen de los ingresos por
                                    obtención de premios</option>
                                <option value="616" @selected(old('regimen_fiscal') == '616')>616 – Sin obligaciones fiscales</option>
                                <option value="621" @selected(old('regimen_fiscal') == '621')>621 – Incorporación Fiscal</option>
                                <option value="625" @selected(old('regimen_fiscal') == '625')>625 – Régimen de las actividades
                                    empresariales con ingresos a través de Plataformas Tecnológicas</option>
                                <option value="626" @selected(old('regimen_fiscal') == '626')>626 – Régimen Simplificado de Confianza
                                    (RESICO)</option>
                            </optgroup>
                            <optgroup label="── Persona Moral / Empresa (RFC de 12 caracteres) ──">
                                <option value="601" @selected(old('regimen_fiscal') == '601')>601 – General de Ley Personas Morales
                                </option>
                                <option value="603" @selected(old('regimen_fiscal') == '603')>603 – Personas Morales con Fines no
                                    Lucrativos</option>
                                <option value="609" @selected(old('regimen_fiscal') == '609')>609 – Consolidación</option>
                                <option value="620" @selected(old('regimen_fiscal') == '620')>620 – Sociedades Cooperativas de
                                    Producción que optan por diferir sus ingresos</option>
                                <option value="622" @selected(old('regimen_fiscal') == '622')>622 – Actividades Agrícolas, Ganaderas,
                                    Silvícolas y Pesqueras</option>
                                <option value="623" @selected(old('regimen_fiscal') == '623')>623 – Opcional para Grupos de
                                    Sociedades</option>
                                <option value="624" @selected(old('regimen_fiscal') == '624')>624 – Coordinados</option>
                                <option value="628" @selected(old('regimen_fiscal') == '628')>628 – Hidrocarburos</option>
                                <option value="629" @selected(old('regimen_fiscal') == '629')>629 – De los Regímenes Fiscales
                                    Preferentes y de las Empresas Multinacionales</option>
                                <option value="630" @selected(old('regimen_fiscal') == '630')>630 – Enajenación de acciones en bolsa
                                    de valores</option>
                            </optgroup>
                        </select>
                        <small>El régimen debe coincidir con el tipo de RFC: 13 caracteres = física, 12 = empresa.</small>
                    </div>

                    <div class="input-group">
                        <label for="codigo_postal">Código Postal del Domicilio Fiscal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" maxlength="5" required
                            value="{{ old('codigo_postal') }}" placeholder="Ej. 76000">
                    </div>

                    <div class="input-group">
                        <label for="uso_cfdi">Uso de CFDI *</label>
                        <select id="uso_cfdi" name="uso_cfdi" required>
                            <option value="">Seleccione...</option>
                            <optgroup label="── Uso general ──">
                                <option value="S01" @selected(old('uso_cfdi') == 'S01')>S01 – Sin efectos fiscales</option>
                                <option value="CP01" @selected(old('uso_cfdi') == 'CP01')>CP01 – Pagos</option>
                            </optgroup>
                            <optgroup label="── Adquisiciones y gastos (con actividad empresarial) ──">
                                <option value="G01" @selected(old('uso_cfdi') == 'G01')>G01 – Adquisición de mercancías
                                </option>
                                <option value="G02" @selected(old('uso_cfdi') == 'G02')>G02 – Devoluciones, descuentos o
                                    bonificaciones</option>
                                <option value="G03" @selected(old('uso_cfdi') == 'G03')>G03 – Gastos en general</option>
                            </optgroup>
                            <optgroup label="── Inversiones ──">
                                <option value="I01" @selected(old('uso_cfdi') == 'I01')>I01 – Construcciones</option>
                                <option value="I02" @selected(old('uso_cfdi') == 'I02')>I02 – Mobiliario y equipo de oficina
                                    por inversiones</option>
                                <option value="I03" @selected(old('uso_cfdi') == 'I03')>I03 – Equipo de transporte</option>
                                <option value="I04" @selected(old('uso_cfdi') == 'I04')>I04 – Equipo de cómputo y accesorios
                                </option>
                                <option value="I05" @selected(old('uso_cfdi') == 'I05')>I05 – Dados, troqueles, moldes,
                                    matrices y herramental</option>
                                <option value="I06" @selected(old('uso_cfdi') == 'I06')>I06 – Comunicaciones telefónicas
                                </option>
                                <option value="I07" @selected(old('uso_cfdi') == 'I07')>I07 – Comunicaciones satelitales
                                </option>
                                <option value="I08" @selected(old('uso_cfdi') == 'I08')>I08 – Otra maquinaria y equipo
                                </option>
                            </optgroup>
                            <optgroup label="── Deducciones personales (personas físicas) ──">
                                <option value="D01" @selected(old('uso_cfdi') == 'D01')>D01 – Honorarios médicos, dentales y
                                    gastos hospitalarios</option>
                                <option value="D02" @selected(old('uso_cfdi') == 'D02')>D02 – Gastos médicos por incapacidad o
                                    discapacidad</option>
                                <option value="D03" @selected(old('uso_cfdi') == 'D03')>D03 – Gastos funerales</option>
                                <option value="D04" @selected(old('uso_cfdi') == 'D04')>D04 – Donativos</option>
                                <option value="D05" @selected(old('uso_cfdi') == 'D05')>D05 – Intereses reales por créditos
                                    hipotecarios</option>
                                <option value="D06" @selected(old('uso_cfdi') == 'D06')>D06 – Aportaciones voluntarias al SAR
                                </option>
                                <option value="D07" @selected(old('uso_cfdi') == 'D07')>D07 – Primas por seguros de gastos
                                    médicos</option>
                                <option value="D08" @selected(old('uso_cfdi') == 'D08')>D08 – Gastos de transportación escolar
                                    obligatoria</option>
                                <option value="D09" @selected(old('uso_cfdi') == 'D09')>D09 – Depósitos en cuentas para el
                                    ahorro, primas de pensiones</option>
                                <option value="D10" @selected(old('uso_cfdi') == 'D10')>D10 – Pagos por servicios educativos
                                    (colegiaturas)</option>
                            </optgroup>
                        </select>
                        <small>Para asalariados (605) usa S01. Para negocios (612) usa G03. Debe ser compatible con el
                            régimen.</small>
                    </div>

                    <div class="input-group">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" id="correo" name="correo" required value="{{ old('correo') }}"
                            placeholder="Ej. cliente@correo.com">
                    </div>
                </div>

                {{-- ================== DATOS DE LA RENTA ================== --}}
                <div class="form-section">
                    <h3>Datos de la Renta</h3>

                    <div class="input-group">
                        <label for="folio_reservacion">Folio de Reservación *</label>
                        <input type="text" id="folio_reservacion" name="folio_reservacion" required
                            value="{{ old('folio_reservacion') }}" placeholder="Ej. R-2025-00123">
                    </div>

                    <div class="input-group">
                        <label for="metodo_pago">Método de Pago *</label>
                        <select id="metodo_pago" name="metodo_pago" required>
                            <option value="">Seleccione...</option>
                            <option value="PUE" @selected(old('metodo_pago') == 'PUE')>PUE – Pago en una sola exhibición</option>
                            <option value="PPD" @selected(old('metodo_pago') == 'PPD')>PPD – Pago en parcialidades o diferido
                            </option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="forma_pago">Forma de Pago *</label>
                        <select id="forma_pago" name="forma_pago" required>
                            <option value="">Seleccione...</option>
                            <option value="01" @selected(old('forma_pago') == '01')>Efectivo</option>
                            <option value="03" @selected(old('forma_pago') == '03')>Transferencia electrónica</option>
                            <option value="04" @selected(old('forma_pago') == '04')>Tarjeta de crédito</option>
                            <option value="28" @selected(old('forma_pago') == '28')>Tarjeta de débito</option>
                            <option value="99" @selected(old('forma_pago') == '99')>Por definir</option>
                        </select>
                    </div>
                </div>

                {{-- ================== CONCEPTO / PRODUCTO SERVICIO ================== --}}
                <div class="form-section">
                    <h3>Concepto Facturable (SAT)</h3>

                    <div class="input-group">
                        <label for="clave_producto">Clave Producto o Servicio (SAT) *</label>
                        <input type="text" id="clave_producto" name="clave_producto"
                            value="{{ old('clave_producto', '90101604') }}" required>
                        <small>Ej. 90101604 – Servicios de renta de automóviles</small>
                    </div>

                    <div class="input-group">
                        <label for="cantidad">Cantidad *</label>
                        <input type="number" id="cantidad" name="cantidad" step="0.01" min="1"
                            value="{{ old('cantidad', '1') }}" required>
                    </div>

                    <div class="input-group">
                        <label for="unidad_sat">Unidad SAT *</label>
                        <input type="text" id="unidad_sat" name="unidad_sat" value="{{ old('unidad_sat', 'E48') }}"
                            required>
                        <small>E48 – Unidad de servicio</small>
                    </div>

                    <div class="input-group">
                        <label for="descripcion">Descripción del Servicio *</label>
                        <textarea id="descripcion" name="descripcion" rows="2" required
                            placeholder="Ej. Renta de vehículo Nissan Versa 2024 (3 días)">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="input-group">
                        <label for="valor_unitario">Valor Unitario * <small>(sin IVA)</small></label>
                        <input type="number" id="valor_unitario" name="valor_unitario" step="0.01" min="0"
                            value="{{ old('valor_unitario') }}" required placeholder="Ej. 3879.31">
                    </div>

                    {{-- IVA y Total: SOLO informativos. Facturapi los calcula. No se envían al controlador. --}}
                    <div class="input-group">
                        <label for="iva_display">IVA (16%)</label>
                        <input type="number" id="iva_display" step="0.01" readonly
                            placeholder="Se calcula automáticamente" style="background:#f0f0f0; cursor:not-allowed;">
                        <small>Calculado automáticamente por el SAT</small>
                    </div>

                    <div class="input-group">
                        <label for="total_display">Importe Total</label>
                        <input type="number" id="total_display" step="0.01" readonly
                            placeholder="Se calcula automáticamente" style="background:#f0f0f0; cursor:not-allowed;">
                        <small>Subtotal + IVA</small>
                    </div>
                </div>

                <div class="factura-footer">
                    <button type="submit" class="btn-facturar">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitud
                    </button>
                    <p class="nota">
                        Tu factura será validada conforme a los lineamientos del SAT y enviada a tu correo electrónico.
                    </p>
                </div>
            </form>
        </div>
    </div>

    {{-- Cálculo automático de IVA y Total (solo visual) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cantidad = document.getElementById('cantidad');
            const valorUnit = document.getElementById('valor_unitario');
            const ivaDisplay = document.getElementById('iva_display');
            const totalDisplay = document.getElementById('total_display');

            function calcular() {
                const cant = parseFloat(cantidad.value) || 0;
                const vu = parseFloat(valorUnit.value) || 0;
                const subtotal = cant * vu;
                const iva = subtotal * 0.16;
                const total = subtotal + iva;

                ivaDisplay.value = iva.toFixed(2);
                totalDisplay.value = total.toFixed(2);
            }

            cantidad.addEventListener('input', calcular);
            valorUnit.addEventListener('input', calcular);
            calcular(); // por si vienen valores de old()
        });
    </script>

@endsection
