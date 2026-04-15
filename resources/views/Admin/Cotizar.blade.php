@extends('layouts.Ventas')
@section('Titulo', 'cotizacionesAdmin')

@section('css-vistaCotizar')
<link rel="stylesheet" href="{{ asset('css/Cotizar.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endsection

@section('contenidoCotizar')

<div class="wrap">
  <main class="main">

    <!-- =========================================
         ENCABEZADO
    ========================================= -->
    <div class="top">
      <h1 class="h1">Nueva cotización</h1>
      <div class="top-actions">
        <button class="btn btn-resumen" id="btnResumen" type="button">
          <span class="pulse-dot"></span> 🧾 Ver resumen de cotización
        </button>
        <button class="btn ghost" onclick="location.href='{{ route('rutaCotizaciones') }}'">Salir</button>
      </div>
    </div>

    <form id="formCotizacion" method="POST" novalidate>
      @csrf
      <input type="hidden" id="categoria_id" name="categoria_id" value="">
      <input type="hidden" id="proteccion_id" name="proteccion_id" value="">
      <input type="hidden" id="tarifa_base" name="tarifa_base" value="">
      <input type="hidden" id="tarifa_modificada" name="tarifa_modificada" value="">
      <input type="hidden" id="tarifa_ajustada" name="tarifa_ajustada" value="0">

      <div id="extrasHidden"></div>
      <div id="individualesHidden"></div>

      <!-- =========================================
           PASO 1: UBICACIÓN
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">📍 Ubicación</div>
          <div class="stack-sub">Selecciona dónde se recoge y se entrega el vehículo.</div>
        </div>

        <div class="stack-body">
          <div class="form-2">
            <!-- RETIRO -->
            <div>
              <label>Sucursal de retiro</label>
              <select id="sucursal_retiro" name="sucursal_retiro" class="input" required>
                <option value="">Selecciona punto de entrega</option>
                @foreach($sucursales as $ciudad => $grupo)
                  @if($ciudad === 'Querétaro')
                    <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
                      @foreach($grupo as $s)
                        <option value="{{ $s->id_sucursal }}"
                                data-ciudad-id="{{ $s->id_ciudad }}"
                                data-nombre="{{ $s->sucursal }}">
                          {{ $s->sucursal }}
                        </option>
                      @endforeach
                    </optgroup>
                  @endif
                @endforeach
              </select>

              <!-- CAMPO VUELO -->
              <div id="campo_vuelo" style="display:none; margin-top:10px;">
                <label>Número de vuelo</label>
                <input type="text" name="numero_vuelo" id="numero_vuelo" class="input" placeholder="Ej. AA1234">
              </div>
            </div>

            <!-- ENTREGA -->
            <div>
              <label>Sucursal de entrega</label>
              <select id="sucursal_entrega" name="sucursal_entrega" class="input" required>
                <option value="">Selecciona punto de devolución</option>
                @foreach($sucursales as $ciudad => $grupo)
                  <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
                    @foreach($grupo as $s)
                      <option value="{{ $s->id_sucursal }}"
                              data-ciudad-id="{{ $s->id_ciudad }}"
                              data-nombre="{{ $s->sucursal }}">
                        {{ $s->sucursal }}
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================
           PASO 2: FECHAS Y HORAS
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🗓️ Fechas y horas</div>
          <div class="stack-sub">Define inicio/fin. Se calculan los días automáticamente.</div>
        </div>

        <div class="stack-body">
          <div class="form-2">
            <!-- FECHA DE SALIDA -->
            <div class="dt-field icon-field">
              <label>Fecha de salida</label>
              <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
              <input id="fecha_inicio_ui" class="input input-lg" type="text" placeholder="Fecha" autocomplete="off">
              <input id="fecha_inicio" type="hidden">
            </div>

            <!-- HORA DE SALIDA -->
            <div class="dt-field icon-field time-field">
              <label>Hora de salida</label>
              <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
              <input id="hora_retiro_ui" class="input input-lg" type="text" placeholder="hh:mm" autocomplete="off">
              <input id="hora_retiro" type="hidden">
            </div>

            <!-- FECHA DE LLEGADA -->
            <div class="dt-field icon-field">
              <label>Fecha de llegada</label>
              <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
              <input id="fecha_fin_ui" class="input input-lg" type="text" placeholder="Fecha" autocomplete="off">
              <input id="fecha_fin" type="hidden">
            </div>

            <!-- HORA DE LLEGADA -->
            <div class="dt-field icon-field time-field">
              <label>Hora de llegada</label>
              <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
              <input id="hora_entrega_ui" class="input input-lg" type="text" placeholder="hh:mm" autocomplete="off">
              <input id="hora_entrega" type="hidden">
            </div>
          </div>

          <div class="days-row">
            <span class="days-pill">⏱️ <b id="diasTxt">0</b> día(s)</span>
          </div>
        </div>
      </section>

      <!-- =========================================
           PASO 3: CATEGORÍA
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🚗 Categoría</div>
          <div class="stack-sub">Selecciona una categoría. Mostramos tarifa base por día + cálculo previo.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnCategorias">📦 Seleccionar categoría</button>
            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="catSelTxt">— Ninguna categoría —</div>
              <div class="picker-sub" id="catSelSub">Tarifa base por día y cálculo previo aparecerán aquí.</div>
            </div>
            <button class="btn gray" type="button" id="catRemove" style="display:none;">✖</button>
          </div>

          <div class="mini-preview" id="catMiniPreview" style="display:none;">
            <div class="mini-right">
              <div class="mini-title" id="catMiniName">—</div>
              <div class="mini-sub" id="catMiniDesc">—</div>
              <div class="mini-price">
                <div>
                  <div class="muted small">Tarifa base</div>
                  <div class="price-big" id="catMiniRate">$0.00 MXN / día</div>
                </div>
                <div>
                  <div class="muted small">Cálculo previo</div>
                  <div class="price-big" id="catMiniCalc">$0.00 MXN</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- =========================================
           PASO 4: PROTECCIONES
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">🔒 Protecciones</div>
          <div class="stack-sub">Elige un paquete de protección.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnProtecciones">🛡️ Seleccionar protección</button>
            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="proteSelTxt">— Ninguna protección —</div>
              <div class="picker-sub" id="proteSelSub">Costo se refleja en el resumen.</div>
            </div>
            <button class="btn gray" type="button" id="proteRemove" style="display:none;">✖</button>
          </div>
        </div>
      </section>

      <!-- =========================================
           PASO 5: ADICIONALES
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">➕ Adicionales</div>
          <div class="stack-sub">Selecciona servicios extra.</div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnAddons">🧩 Seleccionar adicionales</button>
            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="addonsSelTxt">— Ninguno —</div>
              <div class="picker-sub" id="addonsSelSub">Subtotal estimado aparecerá aquí.</div>
            </div>
            <button class="btn gray" type="button" id="addonsClear" style="display:none;">✖</button>
          </div>
        </div>
      </section>

      <!-- =========================================
           PASO 6: CLIENTE
      ========================================= -->
      <section class="stack-card">
        <div class="stack-head">
          <div class="stack-title">👤 Datos del cliente</div>
          <div class="stack-sub">Completa los datos para registrar la cotización.</div>
        </div>

        <div class="stack-body">
          <div class="form-2">
            <div>
              <label>Nombre</label>
              <input id="nombre_cliente" class="input" type="text" required>
            </div>
            <div>
              <label>Apellidos</label>
              <input id="apellidos" class="input" type="text" required>
            </div>
            <div>
              <label>Email</label>
              <input id="email_cliente" class="input" type="email" required>
            </div>
            <div>
              <label>Teléfono</label>
              <input id="telefono_cliente" class="input" type="text" placeholder="+52..." required>
            </div>
            <div>
              <label>País</label>
              <input id="pais" class="input" type="text" value="MÉXICO" required>
            </div>
            <div>
              <label>Vuelo (opcional)</label>
              <input id="no_vuelo" class="input" type="text" placeholder="UA2068">
            </div>
            <div>
              <label>Moneda</label>
              <select id="moneda" class="input">
                <option value="MXN">MXN</option>
                <option value="USD">USD</option>
              </select>
            </div>
            <div>
              <label>Tipo de cambio USD</label>
              <input type="number" id="tc" value="17" step="0.01" class="input">
            </div>
          </div>

          <div class="acciones" style="margin-top: 20px;">
            <button class="btn success" id="btnGuardarYEnviar" type="button">💾 Guardar y enviar</button>
            <button class="btn primary" id="btnConfirmarCotizacion" type="button">✅ Confirmar y reservar</button>
          </div>
        </div>
      </section>

      <!-- BOTÓN VER COTIZACIONES -->
      <div class="ver-cotizaciones-wrap">
        <a href="{{ route('rutaVerCotizaciones') }}" class="btn-ver-cotizaciones" style="text-decoration: none; display: inline-block;">📄 Ver cotizaciones</a>
      </div>

    </form>

  </main>
</div>

<!-- =========================================
     BACKDROP
========================================= -->
<div class="fp-backdrop"></div>


<!-- =========================================
     MODAL: CATEGORÍAS
========================================= -->
<div class="pop modal" id="catPop">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">🚗 Selecciona una categoría</div>
      <button class="btn gray" id="catClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div class="grid-cards" id="categoriasGrid">
        <div class="loading">Cargando categorías...</div>
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="catCancel" type="button">Cerrar</button>
    </footer>
  </div>
</div>

<!-- =========================================
     MODAL: PROTECCIONES (CARRUSEL + DECLINE)
========================================= -->
<div class="pop modal" id="proteccionPop">
  <div class="box modal-box" style="max-width: 1000px;">
    <header class="modal-head">
      <div class="modal-title">🔒 Protecciones</div>
      <button class="btn gray" id="proteClose" type="button">✖</button>
    </header>

    <div class="modal-body" style="padding: 0;">
      <div class="prote-content-wrapper">
        <button type="button" id="btnDeclineModal" class="btn-decline-inline">Decline Protections</button>
        <div id="proteList">
          <div class="loading">Cargando protecciones...</div>
        </div>
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="proteCancel" type="button">Cerrar</button>
    </footer>
  </div>
</div>

<!-- =========================================
     MODAL: DECLINE PROTECTIONS (TÉRMINOS)
========================================= -->
<div id="modalDeclineTerms" class="pop modal" style="display:none; z-index: 10001;">
  <div class="box modal-box" style="max-width: 500px; border: 2px solid #ef4444;">
    <header class="modal-head" style="background: #fff5f5;">
      <div class="modal-title" style="color: #ef4444;">⚠️ Aviso de Responsabilidad</div>
    </header>

    <div class="modal-body">
      <p style="font-size: 14px; margin-bottom: 15px; font-weight: bold; color: #b91c1c;">
        Al declinar las protecciones, usted acepta y entiende lo siguiente:
      </p>
      <ul class="lista-protecciones">
        <li>El cliente es Responsable por el 100% Deducible sobre valor factura de auto.</li>
        <li>No cubre gastos médicos en caso de accidente.</li>
        <li>Asistencia Premium: El cliente es responsable por costos de grúa, corralón, envío de llaves o gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente.</li>
        <li>No cubre Tiempo perdido en taller ni Asistencia Legal.</li>
        <li>Responsabilidad civil limitada hasta 350,000 MXN.</li>
      </ul>
    </div>

    <footer class="modal-foot" style="background: #fff5f5; display: flex; justify-content: flex-end; gap: 10px;">
      <button class="btn gray" id="btnCerrarDeclineTerms" type="button">Cancelar</button>
      <button class="btn danger" id="btnConfirmarDecline" type="button">Aceptar y Seleccionar</button>
    </footer>
  </div>
</div>

<!-- =========================================
     MODAL: ADICIONALES
========================================= -->
<div class="pop modal" id="addonsPop">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">➕ Seleccionar adicionales</div>
      <button class="btn gray" id="addonsClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div id="addonsList" class="grid-cards">
        <div class="loading">Cargando adicionales...</div>
      </div>
    </div>

    <footer class="modal-foot foot-split">
      <button class="btn gray" id="addonsCancel" type="button">Cerrar</button>
      <button class="btn primary" id="addonsApply" type="button">Aplicar</button>
    </footer>
  </div>
</div>

<!-- =========================================
     MODAL: RESUMEN DE COTIZACIÓN
========================================= -->
<div class="pop modal" id="resumenPop">
  <div class="box modal-box resumen-box">
    <header class="modal-head">
      <div class="modal-title">
        <i class='bx bx-spreadsheet' style="vertical-align: middle; margin-right: 5px;"></i>
        Resumen de cotización
      </div>
      <button class="btn gray" id="resumenClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <div class="resumen-card">
        <div class="res-row">
          <div><i class='bx bx-map-pin'></i> Retiro</div>
          <div id="resSucursalRetiro">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-flag'></i> Entrega</div>
          <div id="resSucursalEntrega">—</div>
        </div>

        <div class="res-row">
          <div><i class='bx bx-calendar-event'></i> Salida</div>
          <div id="resFechaInicio">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-time-five'></i> Hora salida</div>
          <div id="resHoraInicio">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-calendar-check'></i> Llegada</div>
          <div id="resFechaFin">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-time'></i> Hora llegada</div>
          <div id="resHoraFin">—</div>
        </div>

        <div class="res-row">
          <div><i class='bx bx-timer'></i> Días</div>
          <div id="resDias">—</div>
        </div>

        <div class="divider"></div>

        <div class="res-row">
          <div>
            <i class='bx bx-money'></i> Tarifa base
            <button type="button" id="btnEditarTarifa" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:16px;margin-left:6px;">
              <i class='bx bx-edit-alt'></i>
            </button>
          </div>
          <div id="resBaseDia">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-calculator'></i> Base × días</div>
          <div id="resBaseTotal">—</div>
        </div>

        <div class="res-row">
          <div><i class='bx bx-shield-quarter'></i> Protección</div>
          <div id="resProte">—</div>
        </div>
        <div class="res-row">
          <div><i class='bx bx-plus-circle'></i> Adicionales</div>
          <div id="resAdds">—</div>
        </div>

        <div class="divider"></div>

        <div class="res-row"><div>Subtotal</div><div id="resSub">$0.00 MXN</div></div>
        <div class="res-row"><div>IVA (16%)</div><div id="resIva">$0.00 MXN</div></div>
        <div class="res-row total"><div>Total</div><div id="resTotal">$0.00 MXN</div></div>
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn primary" type="button" id="resumenOk">Listo</button>
    </footer>
  </div>
</div>

<!-- =========================================
     MODAL: CONFIRMACIÓN
========================================= -->
<div class="pop modal" id="confirmPop" style="display:none;">
  <div class="box modal-box">
    <header class="modal-head">
      <div class="modal-title">✅ Cotización registrada</div>
      <button class="btn gray" id="confirmClose" type="button">✖</button>
    </header>

    <div class="modal-body">
      <p style="margin:0; font-weight:800; color:#111827;">
        ¡Listo! La cotización se registró correctamente.
      </p>
    </div>

    <footer class="modal-foot">
      <button class="btn primary" id="confirmOk" type="button">Aceptar</button>
    </footer>
  </div>
</div>

@endsection

@section('js-vistaCotizar')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<!-- MAPA DE ICONOS -->
<script>
    window.iconosPorId = {
        @foreach($sucursales as $ciudad => $grupo)
            @foreach($grupo as $s)
                @php
                    $nombre = strtolower($s->sucursal);
                    $icono = 'fa-building';

                    if (str_contains($nombre, 'aeropuerto')) {
                        $icono = 'fa-plane-departure';
                    }
                    elseif ((str_contains($nombre, 'central') || str_contains($nombre, 'autobuses')) && !str_contains($nombre, 'plaza central park')) {
                        $icono = 'fa-bus';
                    }
                    elseif (str_contains($nombre, 'oficina') || str_contains($nombre, 'plaza central park') || str_contains($nombre, 'plaza')) {
                        $icono = 'fa-building';
                    }
                @endphp
                {{ $s->id_sucursal }}: '{{ $icono }}',
            @endforeach
        @endforeach
    };
    console.log('✅ Iconos cargados:', window.iconosPorId);
</script>

<script src="{{ asset('js/Cotizar.js') }}"></script>
@endsection
