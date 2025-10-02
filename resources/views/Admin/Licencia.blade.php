@extends('layouts.Ventas')
@section('Titulo', 'Licencia')
    @section('css-vistaLicencia')
        <link rel="stylesheet" href="{{ asset('css/Licencia.css') }}">
    @endsection
@section('contenidoLicencia')
    <!-- Contenido -->
  <main class="main">
    <div class="header">
      <div>
        <h1 class="h1">Paso 2 · Licencia de conducir</h1>
        <p class="subtitle" id="subCliente">Captura/valida la licencia. Se vincula al cliente del paso anterior.</p>
      </div>
      <div class="row">
        <button class="btn ghost" id="btnBack" type="button">Regresar</button>
      </div>
    </div>

    <section class="card">
      <!-- Acciones -->
      <div class="row" style="justify-content:center;margin-bottom:10px">
        <button class="btn primary" id="btnBuscar" type="button">Buscar</button>
        <button class="btn danger" id="btnLimpiar" type="button">Limpiar</button>
      </div>

      <!-- Visual principal -->
      <div class="big" style="margin-bottom:14px">
        <div class="tag">AWD</div>
        <div class="num" id="numPreview">—</div>
      </div>

      <!-- Formulario -->
      <h3 class="section-title">Datos de la licencia</h3>
      <div class="grid-3">
        <div class="field"><label>Número</label><input id="licNumero" class="input" placeholder="Ej. ABC123456"/></div>
        <div class="field"><label>Vence (expira)</label><input id="licVence" type="date" class="input"/></div>
        <div class="field"><label>Estado (State)</label><input id="licEstado" class="input" placeholder="Ej. Querétaro"/></div>
        <div class="field"><label>País (Country)</label><select id="licPais" class="select"><option>México</option><option>USA</option><option>Canadá</option></select></div>
        <div class="field"><label>Clase/Categoría</label><input id="licClase" class="input" placeholder="Opcional"/></div>
        <div class="field"><label>Restricciones</label><input id="licRestr" class="input" placeholder="Opcional"/></div>
      </div>

      <!-- Botonera -->
      <div class="row" style="justify-content:flex-end;margin-top:16px">
        <button class="btn primary" id="btnGuardar" type="button" disabled>Guardar</button>
        <button class="btn primary" id="btnContinuar" type="button" disabled>Continuar a Paso 3</button>
      </div>
    </section>
  </main>
    @section('js-vistaLicencia')
        <script src="{{ asset('js/Licencia.js') }}"></script>
    @endsection
@endsection
