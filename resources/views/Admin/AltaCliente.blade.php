@extends('layouts.Ventas')
@section('Titulo', 'Alta Cliente')
    @section('css-vistaAltaCliente')
        <link rel="stylesheet" href="{{ asset('css/AltaCliente.css') }}">
    @endsection

    @section('contenidoAltaCliente')
     <main class="main">
    <div class="header">
      <div>
        <h1 class="h1">Paso 1 · Alta de cliente</h1>
        <p class="subtitle">Captura los datos del arrendatario. Se guarda automáticamente en este equipo.</p>
      </div>
      <div class="row">
        <button class="btn ghost" id="btnBack" type="button">Regresar</button>
      </div>
    </div>

    <!-- Resumen en vivo -->
    <section class="card">
      <h3 class="section-title">Resumen</h3>
      <div class="resume" id="resumen">
        <div class="rbox"><div class="t">ID cliente</div><div class="v" id="r_id">—</div></div>
        <div class="rbox"><div class="t">Nombre</div><div class="v" id="r_nombre">—</div><small id="r_nac"></small></div>
        <div class="rbox"><div class="t">Contacto</div><div class="v" id="r_contacto">—</div><small id="r_email"></small></div>
        <div class="rbox"><div class="t">Domicilio</div><div class="v" id="r_dom">—</div><small id="r_cp"></small></div>
        <div class="rbox"><div class="t">Licencia</div><div class="v" id="r_lic">—</div><small id="r_licvig"></small></div>
        <div class="rbox"><div class="t">Fiscal</div><div class="v" id="r_fiscal">—</div><small id="r_cfdi"></small></div>
        <div class="rbox"><div class="t">Notas</div><div class="v" id="r_notas">—</div></div>
        <div class="rbox"><div class="t">Registro</div><div class="v" id="r_fecha">—</div></div>
      </div>
    </section>

    <section class="card">
      <!-- barra acciones -->
      <div class="bar">
        <button class="btn danger" id="btnClear" type="button">Limpiar</button>
        <button class="btn primary" id="btnSave" type="button" disabled>Registrar y continuar</button>
      </div>

      <!-- Identificación -->
      <h3 class="section-title">Identificación</h3>
      <div class="grid-4">
        <div class="field">
          <label>No. cliente (sugerido)</label>
          <input id="noCliente" class="input"/>
          <div class="helper">Se genera automáticamente; puedes editarlo.</div>
        </div>
        <div class="field">
          <label>Fecha de registro</label>
          <input id="fechaReg" type="date" class="input"/>
        </div>
        <div class="field">
          <label>País</label>
          <select id="pais" class="select"><option>México</option><option>USA</option><option>Canadá</option></select>
        </div>
        <div class="field">
          <label>Estado</label>
          <select id="estado" class="select"><option>CDMX</option><option>Querétaro</option><option>Jalisco</option></select>
        </div>
      </div>

      <!-- Datos del arrendatario -->
      <h3 class="section-title" style="margin-top:16px">Datos del arrendatario</h3>
      <div class="grid-3">
        <div class="field"><label>Nombre(s)</label><input id="nombres" class="input" placeholder="Nombre"/></div>
        <div class="field"><label>Apellido paterno</label><input id="apPat" class="input"/></div>
        <div class="field"><label>Apellido materno</label><input id="apMat" class="input"/></div>
        <div class="field"><label>Fecha de nacimiento</label><input id="fnac" type="date" class="input"/></div>
        <div class="field"><label>Celular</label><input id="cel" class="input" placeholder="10 dígitos"/></div>
        <div class="field"><label>Email</label><input id="email" type="email" class="input"/></div>
      </div>

      <div class="grid-3" style="margin-top:10px">
        <div class="field"><label>Calle y número</label><input id="calle" class="input"/></div>
        <div class="field"><label>Colonia</label><input id="colonia" class="input"/></div>
        <div class="field"><label>Municipio/Alcaldía</label><input id="mun" class="input"/></div>
        <div class="field"><label>CP</label><input id="cp" class="input" placeholder="5 dígitos"/></div>
        <div class="field"><label>Notas internas</label><textarea id="notas" class="txta" placeholder="Notas, restricciones, responsabilidades…"></textarea></div>
      </div>

      <!-- Licencia (OPCIONAL aquí; obligatoria en paso 2) -->
      <h3 class="section-title" style="margin-top:16px">Licencia de conducir</h3>
      <div class="grid-4">
        <div class="field"><label>No. licencia</label><input id="lic" class="input" placeholder="Ej. ABC123456"/></div>
        <div class="field"><label>País emisión</label><select id="licPais" class="select"><option>México</option><option>USA</option><option>Canadá</option></select></div>
        <div class="field"><label>Vigencia</label><input id="licVig" type="date" class="input"/></div>
        <div class="field"><label>&nbsp;</label><button class="btn ghost" id="btnBuscar" type="button">Buscar</button></div>
      </div>

      <!-- Fiscales (opcional) -->
      <h3 class="section-title" style="margin-top:16px">Datos fiscales (opcional)</h3>
      <div class="grid-3">
        <div class="field"><label>RFC</label><input id="rfc" class="input" placeholder="13 caracteres máx."/></div>
        <div class="field"><label>Razón social</label><input id="razon" class="input"/></div>
        <div class="field"><label>Uso CFDI</label><select id="cfdi" class="select"><option value="">—</option><option>G03</option><option>P01</option></select></div>
      </div>
      <div class="grid-3">
        <div class="field"><label>Domicilio fiscal</label><input id="domFiscal" class="input" placeholder="Calle, No., Col., CP, Municipio"/></div>
        <div class="field"><label>Correo facturación</label><input id="mailFiscal" type="email" class="input" placeholder="facturas@empresa.com"/></div>
      </div>

      <!-- barra inferior -->
      <div class="bar">
        <button class="btn danger" id="btnClear2" type="button">Limpiar</button>
        <button class="btn primary" id="btnSave2" type="button" disabled>Registrar y continuar</button>
      </div>
    </section>
  </main>
        @section('js-vistaAltaCliente')
            <script src="{{ asset('js/AltaCliente.js') }}" defer></script>

        @endsection

@endsection
