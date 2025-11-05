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
    @if(session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('rutaFacturar') }}" method="POST" class="factura-form">
      @csrf

      {{-- ================== DATOS DEL CLIENTE ================== --}}
      <div class="form-section">
        <h3>Datos del Cliente (Receptor)</h3>

        <div class="input-group">
          <label for="nombre">Nombre o Razón Social *</label>
          <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez López">
        </div>

        <div class="input-group">
          <label for="rfc">RFC *</label>
          <input type="text" id="rfc" name="rfc" maxlength="13" required placeholder="Ej. PELJ800101ABC">
        </div>

        <div class="input-group">
          <label for="regimen">Régimen Fiscal *</label>
          <select id="regimen" name="regimen" required>
            <option value="">Seleccione...</option>
            <option value="601">601 – General de Ley Personas Morales</option>
            <option value="605">605 – Sueldos y Salarios</option>
            <option value="612">612 – Personas Físicas con Actividades Empresariales</option>
            <option value="616">616 – Sin obligaciones fiscales</option>
          </select>
        </div>

        <div class="input-group">
          <label for="cp">Código Postal del Domicilio Fiscal *</label>
          <input type="text" id="cp" name="cp" maxlength="5" required placeholder="Ej. 76000">
        </div>

        <div class="input-group">
          <label for="uso_cfdi">Uso de CFDI *</label>
          <select id="uso_cfdi" name="uso_cfdi" required>
            <option value="">Seleccione...</option>
            <option value="G01">G01 – Adquisición de mercancías</option>
            <option value="G03">G03 – Gastos en general</option>
            <option value="P01">P01 – Por definir</option>
          </select>
        </div>

        <div class="input-group">
          <label for="correo">Correo electrónico *</label>
          <input type="email" id="correo" name="correo" required placeholder="Ej. cliente@correo.com">
        </div>
      </div>

      {{-- ================== DATOS DE LA RENTA ================== --}}
      <div class="form-section">
        <h3>Datos de la Renta</h3>

        <div class="input-group">
          <label for="folio">Folio de Reservación *</label>
          <input type="text" id="folio" name="folio" required placeholder="Ej. R-2025-00123">
        </div>

        <div class="input-group">
          <label for="metodo_pago">Método de Pago *</label>
          <select id="metodo_pago" name="metodo_pago" required>
            <option value="">Seleccione...</option>
            <option value="PUE">PUE – Pago en una sola exhibición</option>
            <option value="PPD">PPD – Pago en parcialidades o diferido</option>
          </select>
        </div>

        <div class="input-group">
          <label for="forma_pago">Forma de Pago *</label>
          <select id="forma_pago" name="forma_pago" required>
            <option value="">Seleccione...</option>
            <option value="01">Efectivo</option>
            <option value="03">Transferencia electrónica</option>
            <option value="04">Tarjeta de crédito</option>
            <option value="28">Tarjeta de débito</option>
            <option value="99">Por definir</option>
          </select>
        </div>
      </div>

      {{-- ================== CONCEPTO / PRODUCTO SERVICIO ================== --}}
      <div class="form-section">
        <h3>Concepto Facturable (SAT)</h3>

        <div class="input-group">
          <label for="clave_sat">Clave Producto o Servicio (SAT) *</label>
          <input type="text" id="clave_sat" name="clave_sat" value="90101604" required>
          <small>Ej. 90101604 – Servicios de renta de automóviles</small>
        </div>

        <div class="input-group">
          <label for="cantidad">Cantidad *</label>
          <input type="number" id="cantidad" name="cantidad" step="0.01" value="1" required>
        </div>

        <div class="input-group">
          <label for="unidad_sat">Unidad SAT *</label>
          <input type="text" id="unidad_sat" name="unidad_sat" value="E48" required>
          <small>E48 – Unidad de servicio</small>
        </div>

        <div class="input-group">
          <label for="descripcion">Descripción del Servicio *</label>
          <textarea id="descripcion" name="descripcion" rows="2" required placeholder="Ej. Renta de vehículo Nissan Versa 2024 (3 días)"></textarea>
        </div>

        <div class="input-group">
          <label for="valor_unitario">Valor Unitario *</label>
          <input type="number" id="valor_unitario" name="valor_unitario" step="0.01" required placeholder="Ej. 3879.31">
        </div>

        <div class="input-group">
          <label for="iva">IVA (16%) *</label>
          <input type="number" id="iva" name="iva" step="0.01" required placeholder="Ej. 620.69">
        </div>

        <div class="input-group">
          <label for="total">Importe Total *</label>
          <input type="number" id="total" name="total" step="0.01" required placeholder="Ej. 4500.00">
        </div>
      </div>

      <div class="factura-footer">
        <button type="submit" class="btn-facturar">
          <i class="fas fa-paper-plane"></i> Enviar Solicitud
        </button>
        <p class="nota">
          Tu factura será validada conforme a los lineamientos del SAT y enviada a tu correo electrónico en un plazo máximo de 24 horas.
        </p>
      </div>
    </form>
  </div>
</div>

@endsection
