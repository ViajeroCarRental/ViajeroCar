@extends('layouts.Ventas')
@section('Titulo', 'RFC Fiscal')
    @section('css-vistaRFC-Fiscal')
        <link rel="stylesheet" href="{{ asset('css/RFC-Fiscal.css') }}">
    @endsection
@section('contenidoRFC-Fiscal')
    <main class="main">
    <div class="header">
      <div>
        <h1 class="h1">Paso 3 · Datos fiscales</h1>
        <p class="subtitle">Completa los datos de facturación del cliente seleccionado.</p>
      </div>
      <div class="row">
        <button class="btn ghost" id="btnBack" type="button">Regresar</button>
      </div>
    </div>

    <!-- Resumen -->
    <section class="card">
      <div class="resume">
        <div class="badge">🆔 Cliente: <strong id="cliId">—</strong></div>
        <div class="badge">🧾 RFC: <strong id="rfctag">—</strong></div>
        <div class="badge">🏢 Razón social: <strong id="razontag">—</strong></div>
      </div>
    </section>

    <!-- Acciones rápidas -->
    <section class="card">
      <div class="row" style="justify-content:space-between;align-items:center">
        <div class="row">
          <button class="btn ok" id="btnLast">Tomar último cliente</button>
          <button class="btn danger" id="btnClear">Limpiar</button>
        </div>
        <div class="row">
          <button class="btn primary" id="btnGuardar" disabled>Guardar</button>
          <button class="btn primary" id="btnFinalizar" disabled>Finalizar</button>
        </div>
      </div>
    </section>

    <!-- Datos fiscales -->
    <section class="card">
      <h3 class="section-title">Identificación fiscal</h3>
      <div class="grid-3">
        <div class="field"><label>RFC</label><input id="rfc" class="input" placeholder="13 caracteres"/></div>
        <div class="field"><label>Razón social</label><input id="razon" class="input"/></div>
        <div class="field"><label>Uso CFDI</label>
          <select id="cfdi" class="select">
            <option value="">—</option>
            <option value="G03">G03 - Gastos en general</option>
            <option value="P01">P01 - Por definir</option>
            <option value="D01">D01 - Honorarios médicos</option>
          </select>
        </div>
      </div>

      <h3 class="section-title" style="margin-top:16px">Domicilio fiscal</h3>
      <div class="grid-3">
        <div class="field"><label>Calle</label><input id="calle" class="input"/></div>
        <div class="field"><label>Número exterior</label><input id="numext" class="input"/></div>
        <div class="field"><label>Número interior (opcional)</label><input id="numint" class="input"/></div>
        <div class="field"><label>Referencia (opcional)</label><input id="refer" class="input"/></div>
        <div class="field"><label>Colonia</label><input id="colonia" class="input"/></div>
        <div class="field"><label>Código Postal</label><input id="cp" class="input" placeholder="5 dígitos"/></div>
        <div class="field"><label>Municipio</label><input id="municipio" class="input"/></div>
        <div class="field"><label>Ciudad</label><input id="ciudad" class="input"/></div>
        <div class="field"><label>Estado</label><input id="estado" class="input"/></div>
        <div class="field"><label>País</label>
          <select id="pais" class="select"><option>México</option><option>USA</option><option>Canadá</option></select>
        </div>
      </div>

      <div class="grid-2" style="margin-top:16px">
        <div class="field"><label>Correo para facturación</label><input id="correo" type="email" class="input" placeholder="facturas@empresa.com"/></div>
        <div class="field"><label>Notas</label><input id="notas" class="input" placeholder="Opcional"/></div>
      </div>
    </section>
  </main>
    @section('js-vistaRFC-Fiscal')
        <script src="{{ asset('js/RFC-Fiscal.js') }}"></script>
    @endsection
@endsection
