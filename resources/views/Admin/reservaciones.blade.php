@extends('layouts.Ventas')
@section('Titulo', 'reservacionesAdmin')

@section('css-vistaHomeVentas')
<link rel="stylesheet" href="{{ asset('css/reservacionesAdmin.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Cabin:wght@400;600;700&display=swap" rel="stylesheet">
@endsection

@section('contenidoreservacionesAdmin')

<div class="wrap">
  <main class="main">

    <!-- 🔹 Encabezado superior -->
    <div class="top">
      <h1 class="h1">Nueva reservación</h1>
      <button class="btn ghost" onclick="location.href='../dashboard.html'">Salir</button>
    </div>

    <!-- 🔹 Layout principal -->
<div class="grid">

  <!-- ======================
       SECCIÓN IZQUIERDA
  ======================= -->
  <section class="steps">

    <!-- 🧭 PASO 1: VIAJE -->
    <article class="step" data-step="1">
      <header>
        <div class="badge">1</div>
        <h3>PASO 1 · Viaje</h3>
      </header>

      <div class="body">

        <!-- Grupo: Entrega y Devolución -->
        <div class="form-2">
          <div>
            <label>Sucursal de retiro</label>
            <select id="sucursal_retiro" name="sucursal_retiro" class="input">
              <option value="">Selecciona punto de entrega</option>
              @foreach($sucursales as $s)
                <option value="{{ $s->id_sucursal }}">{{ $s->nombre_mostrado }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label>Sucursal de entrega</label>
            <select id="sucursal_entrega" name="sucursal_entrega" class="input">
              <option value="">Selecciona punto de devolución</option>
              @foreach($sucursales as $s)
                <option value="{{ $s->id_sucursal }}">{{ $s->nombre_mostrado }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Grupo: Fechas -->
        <div class="form-2">
          <div>
            <label>Fecha de salida</label>
            <input id="fecha_inicio" name="fecha_inicio" class="input" type="date" required>
          </div>
          <div>
            <label>Hora de salida</label>
            <input id="hora_retiro" name="hora_retiro" class="input" type="time" required>
          </div>
          <div>
            <label>Fecha de llegada</label>
            <input id="fecha_fin" name="fecha_fin" class="input" type="date" required>
          </div>
          <div>
            <label>Hora de llegada</label>
            <input id="hora_entrega" name="hora_entrega" class="input" type="time" required>
          </div>
        </div>

        <!-- Grupo: Categoría -->
        <div>
          <label>Categoría del vehículo</label>
          <select id="categoriaSelect" class="input">
            <option value="0">Selecciona una categoría</option>
            @foreach($categorias as $cat)
              <option value="{{ $cat->id_categoria }}">{{ $cat->nombre }}</option>
            @endforeach
          </select>
        </div>
        <!-- Grupo: Duración del viaje -->
<div style="margin-top:10px;">
  <span id="diasBadge" class="badge" style="background:#ECFDF3;border:1px solid #ABEFC6;color:var(--ok)">
    0 día(s)
  </span>
</div>

        <!-- Botón continuar -->
        <div class="acciones">
          <button class="btn primary" id="go2" type="button">Continuar →</button>
        </div>

      </div>
    </article>

    <!-- 🚗 PASO 2: VEHÍCULO Y OPCIONES -->
    <article class="step" data-step="2" style="display:none;">
      <header>
        <div class="badge">2</div>
        <h3>PASO 2 · Vehículo y opciones</h3>
      </header>

      <div class="body">

        <!-- Grupo: Protecciones -->
        <div class="form-2">
          <div>
            <label>Protecciones (Seguros)</label>
            <div class="flex" style="gap:8px;align-items:center;">
              <button class="btn primary" type="button" id="btnProtecciones">🔒 Seleccionar protección</button>
              <input id="proteccionSel" name="proteccionSel" class="input" type="text" placeholder="Ninguna protección seleccionada" readonly>
              <button id="proteRemove" class="btn gray" type="button" style="display:none;">✖</button>
            </div>
          </div>
        </div>

        <!-- Grupo: Adicionales -->
        <h4 style="margin:10px 0;">Adicionales</h4>
        <div id="addGrid" class="add-grid">
          <div class="loading" style="text-align:center;padding:12px;">Cargando adicionales...</div>
        </div>

        <!-- Grupo: Moneda -->
        <div class="form-2" style="margin-top:20px;">
          <div>
            <label>Moneda</label>
            <div class="flex">
              <select id="moneda" name="moneda" class="input" style="max-width:160px;">
                <option value="MXN">MXN</option>
                <option value="USD">USD</option>
              </select>
              <span class="badge">
                <b>TC USD</b>
                <input type="number" id="tc" value="17" min="0.01" step="0.01"
                       style="width:90px;padding:6px 8px;border:1px solid #D0D5DD;border-radius:8px;">
              </span>
            </div>
          </div>
        </div>

        <!-- Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back1" type="button">← Atrás</button>
          <button class="btn primary" id="go3" type="button">Continuar →</button>
        </div>

      </div>
    </article>

    <!-- 👤 PASO 3: CLIENTE Y ENVÍO -->
    <article class="step" data-step="3" style="display:none;">
      <header>
        <div class="badge">3</div>
        <h3>PASO 3 · Datos del cliente</h3>
      </header>

      <div class="body">
        <!-- ✅ Formulario de datos del cliente -->
        <form id="formReserva" action="{{ route('reservaciones.guardar') }}" method="POST" class="form-2" novalidate>
  @csrf

  <!-- Campos visibles -->
  <div>
    <label>Nombre</label>
    <input id="nombre_cliente" name="nombre_cliente" class="input" type="text" required>
  </div>

  <div>
    <label>Apellido(s)</label>
    <input id="apellidos" name="apellidos" class="input" type="text" required>
  </div>

  <div>
    <label>Email</label>
    <input id="email_cliente" name="email_cliente" class="input" type="email" required>
  </div>

  <div>
    <label>Teléfono</label>
    <input id="telefono_cliente" name="telefono_cliente" class="input" type="text" placeholder="+52..." required>
  </div>

  <div>
    <label>País</label>
    <input id="pais" name="pais" class="input" type="text" value="MÉXICO" required>
  </div>

  <div>
    <label>Vuelo</label>
    <input id="no_vuelo" name="no_vuelo" class="input" type="text" placeholder="UA2068" required>
  </div>
  <!-- Botones -->
  <div class="acciones" style="grid-column:1/-1; margin-top:20px;">
    <button class="btn gray" id="back2" type="button">← Atrás</button>
    <button class="btn primary" id="btnReservar" type="submit">✅ Registrar reservación</button>
  </div>
</form>


        <!-- 📄 Compartir / Imprimir -->
        <div class="compartir">
          <p class="small">Opciones</p>
          <div class="iconbar">
            <button class="icon-btn" id="btnPrint" title="Imprimir / PDF">
              <img src="{{ asset('assets/') }}" alt="print">
            </button>
          </div>
        </div>
      </div>
    </article>

  </section>

  <!-- ======================
     SECCIÓN DERECHA: RESUMEN
======================= -->
<aside class="sticky">
  <div class="card">
    <div class="head">Resumen de Reservación</div>
    <div class="cnt">

      <!-- 🚗 Imagen de referencia por categoría -->
      <div id="vehImageWrap" style="text-align:center;margin-bottom:10px;display:none;">
        <img id="vehImage"
             src="{{ asset('assets/placeholder-car.jpg') }}"
             alt="Ejemplo de vehículo de la categoría seleccionada"
             style="width:100%;max-width:250px;border-radius:12px;object-fit:cover;">
        <div id="vehName" style="font-weight:700;margin-top:6px;">
          Ejemplo de la categoría seleccionada
        </div>
      </div>

      <!-- 📅 Detalles del viaje -->
      <div class="trip-details" style="margin-bottom:12px;">
        <div class="row"><div>📍 Retiro</div><div id="resSucursalRetiro">—</div></div>
        <div class="row"><div>🏁 Entrega</div><div id="resSucursalEntrega">—</div></div>
        <div class="row"><div>🗓️ Fecha salida</div><div id="resFechaInicio">—</div></div>
        <div class="row"><div>🕑 Hora salida</div><div id="resHoraInicio">—</div></div>
        <div class="row"><div>📅 Fecha llegada</div><div id="resFechaFin">—</div></div>
        <div class="row"><div>🕓 Hora llegada</div><div id="resHoraFin">—</div></div>
        <div class="row"><div>⏱️ Duración</div><div id="resDias">—</div></div>
      </div>

      <!-- 💰 Detalle de precios -->
      <div class="row">
        <div>
          Tarifa Base
          <button id="editTarifa" title="Editar tarifa"
                  style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:16px;margin-left:6px;">
            ✏️
          </button>
        </div>
        <div id="baseLine">—</div>
      </div>

      <div class="row"><div>Protección</div><div id="proteName">—</div></div>
      <div class="row"><div>Adicionales</div><div id="extrasName">—</div></div>
      <div class="row"><div>Subtotal</div><div id="subTot">$0.00 MXN</div></div>
      <div class="row"><div>IVA (16%)</div><div id="iva">$0.00 MXN</div></div>
      <div class="row">
        <div style="font-weight:900">Total</div>
        <div class="total" id="total">$0.00 MXN</div>
      </div>

    </div>
  </div>
</aside>


</div>
 <!-- /grid -->

   <!-- ======================
     MODALES (EN LA MISMA VISTA)
======================= -->

<!-- 📅 Date Picker -->
<div class="pop" id="dpPop">
  <div class="box" style="width:min(380px,96vw)">
    <div class="dp-head" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--brand);color:#fff;">
      <button class="btn gray" id="dpPrev" style="background:#fff;color:var(--brand);border:none;">◀</button>
      <div class="dp-title" id="dpMonth" style="font-weight:900;">—</div>
      <button class="btn gray" id="dpNext" style="background:#fff;color:var(--brand);border:none;">▶</button>
    </div>

    <div id="dpGrid"></div>

    <footer style="display:flex;justify-content:space-around;padding:10px;border-top:1px solid #eee;">
      <button class="btn gray" id="dpToday">Hoy</button>
      <button class="btn gray" id="dpClear">Limpiar</button>
      <button class="btn primary" id="dpApply">Aplicar</button>
    </footer>
  </div>
</div>

<!-- 🕑 Time Picker -->
<div class="pop" id="tpPop">
  <div class="box tpbox" style="width:min(420px,96vw)">
    <div class="tp-head" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--brand);color:#fff;">
      <div class="tp-title" style="font-weight:900;">Selecciona hora</div>
      <div style="display:flex;gap:6px;align-items:center;">
        <button class="btn gray" id="tp12" type="button" style="background:#fff;color:var(--brand);">12h</button>
        <button class="btn gray" id="tp24" type="button" style="background:#fff;color:var(--brand);">24h</button>
        <button class="btn gray" id="tpAM" type="button" style="background:#fff;color:var(--brand);">AM</button>
        <button class="btn gray" id="tpPM" type="button" style="background:#fff;color:var(--brand);">PM</button>
        <button class="btn gray" id="tpClose" type="button" style="background:#fff;color:var(--brand);">✖</button>
      </div>
    </div>

    <div class="tp-wrap" style="padding:16px;display:flex;flex-direction:column;gap:14px;">
      <div class="tp-input" style="display:flex;align-items:center;gap:10px;">
        <label class="small" for="tpSelect" style="font-weight:700;color:#344054;min-width:90px;">Seleccionar</label>
        <select id="tpSelect" class="input" style="flex:1;"></select>
      </div>

      <div class="tp-quick" style="display:flex;flex-wrap:wrap;gap:6px;font-size:14px;">
        <span class="tp-q" data-q="07:00 AM">Mañana 07:00 AM</span>
        <span class="tp-q" data-q="09:00 AM">Mañana 09:00 AM</span>
        <span class="tp-q" data-q="12:00 PM">Mediodía 12:00 PM</span>
        <span class="tp-q" data-q="04:00 PM">Tarde 04:00 PM</span>
        <span class="tp-q" data-q="08:00 PM">Noche 08:00 PM</span>
        <span class="tp-q" data-q="+30">+30 min</span>
        <span class="tp-q" data-q="now">Ahora</span>
      </div>
    </div>

    <footer style="display:flex;justify-content:space-around;padding:10px;border-top:1px solid #eee;">
      <button class="btn gray" id="tpClear">Limpiar</button>
      <button class="btn primary" id="tpApply">Aplicar</button>
    </footer>
  </div>
</div>

    <!-- 🔒 Modal de Protecciones -->
<div class="pop" id="proteccionPop">
  <div class="box" style="width:min(750px,96vw)">
    <header style="display:flex;align-items:center;justify-content:space-between;">
      <span style="font-weight:700;">Seleccionar protección / paquete de seguro</span>
      <button class="btn gray" id="proteClose">✖</button>
    </header>

    <div id="proteList" class="prote-grid" style="padding:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
      <!-- Se cargan las tarjetas dinámicamente -->
      <div class="loading" style="text-align:center;padding:20px;">Cargando paquetes...</div>
    </div>

    <footer style="padding:10px;text-align:right;border-top:1px solid #eee;">
      <button class="btn gray" id="proteCancel">Cerrar</button>
    </footer>
  </div>
</div>



  </main>
</div>

@section('js-vistareservacionesAdmin')
<script src="{{ asset('js/reservacionesAdmin.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
@endsection

@endsection
