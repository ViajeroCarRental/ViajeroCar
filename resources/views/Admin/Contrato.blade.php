@extends('layouts.Ventas')
@section('Titulo', 'Contrato')

@section('css-vistaContrato')
<link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')
<main class="main"
  id="contratoApp"
  data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
  data-numero="{{ $contrato->numero_contrato ?? '' }}"
  data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}">


  <h1 class="h1">Gestión de Contrato</h1>
  <p style="color:#666; margin-bottom:10px;">
  <b>No. Contrato:</b> {{ $contrato->numero_contrato ?? '—' }}
</p>

  <div class="grid">

    <!-- ======================
         SECCIÓN IZQUIERDA
    ======================= -->
    <section class="steps">
      <!-- 🧾 PASO 1: DATOS DE RESERVACIÓN -->
<article class="step active" data-step="1">
  <header>
    <div class="badge">1</div>
    <h3>PASO 1 · Datos de la Reservación</h3>
  </header>

  <div class="body contrato-resumen" id="contratoInicial"
    data-id-contrato="{{ $contrato->id_contrato ?? '' }}"
    data-id-reservacion="{{ $reservacion->id_reservacion ?? '' }}"
    data-codigo="{{ $reservacion->codigo ?? '' }}"
    data-nombre="{{ $reservacion->nombre_cliente ?? '' }}"
    data-email="{{ $reservacion->email_cliente ?? '' }}"
    data-telefono="{{ $reservacion->telefono_cliente ?? '' }}"
    data-inicio="{{ $reservacion->fecha_inicio ?? '' }}"
    data-fin="{{ $reservacion->fecha_fin ?? '' }}"
    data-hora-retiro="{{ $reservacion->hora_retiro ?? '' }}"
    data-hora-entrega="{{ $reservacion->hora_entrega ?? '' }}"
    data-total="{{ $reservacion->total ?? '' }}">


    <!-- 🔹 Cabecera -->
    <div class="card resumen-header">
      <div class="row">
        <div>
          <h4>Código de reservación</h4>
          <p id="codigo">{{ strtoupper($reservacion->codigo) }}</p>
        </div>
        <div>
          <h4>Titular de la reservación</h4>
          <p id="clienteNombre">{{ strtoupper($reservacion->nombre_cliente ?? '—') }}</p>
        </div>
      </div>
    </div>

    <!-- 🔹 Entrega y devolución -->
    <div class="resumen-entrega">

      <!-- ENTREGA -->
      <div class="bloque entrega">
        <div class="titulo">ENTREGA</div>
        <p class="lugar">Sucursal de origen</p>
<!-------------------------------------------------------------------------------------------------------->
<!--1374, contrato controller envia el mensaje al admin -->
 @php
    $fechaMostrar = \Carbon\Carbon::parse($reservacion->fecha_inicio);
@endphp

<!-- FECHA MOSTRADA -->
<div class="fecha fecha-entrega-display">
    <div class="dia">{{ $fechaMostrar->format('d') }}</div>
    <div class="mes">{{ strtoupper($fechaMostrar->format('M')) }}</div>
    <div class="anio">{{ $fechaMostrar->format('Y') }}</div>

<!-------------------------------------------------------------------------------------------------------->

          <!-- ✏️ Lápiz (solicitar cambio) -->
          <span class="edit-icon fecha-entrega-edit" title="Solicitar cambio">
            ✏️
          </span>
        </div>

        <!-- INPUT PARA SOLICITAR CAMBIO (deshabilitado hasta aprobación) -->
        <div class="fecha-edicion-entrega" style="display:none; margin-top:8px;">
    <input type="date" id="nuevaFechaEntrega" disabled>
    <input type="time" id="nuevaHoraEntrega" disabled>

    <!-- ESTE ES EL BOTÓN CORRECTO -->
    <button type="button" class="btn small" id="btnSolicitarCambioEntrega">
        Solicitar autorización
    </button>
</div>

        <div class="hora">{{ \Carbon\Carbon::parse($reservacion->hora_retiro)->format('h:i A') }}</div>
      </div>

      <!-- DEVOLUCIÓN -->
      <div class="bloque devolucion">
        <div class="titulo">DEVOLUCIÓN</div>
        <p class="lugar">Sucursal destino</p>

        <!-- FECHA ACTUAL -->
        <div class="fecha fecha-devolucion-display">
          <div class="dia">{{ \Carbon\Carbon::parse($reservacion->fecha_fin)->format('d') }}</div>
          <div class="mes">{{ strtoupper(\Carbon\Carbon::parse($reservacion->fecha_fin)->format('M')) }}</div>
          <div class="anio">{{ \Carbon\Carbon::parse($reservacion->fecha_fin)->format('Y') }}</div>

          <!-- ✏️ Lápiz (cambio directo) -->
          <span class="edit-icon fecha-devolucion-edit" title="Editar fecha de devolución">
            ✏️
          </span>
        </div>

        <!-- INPUT PARA EDITAR DIRECTAMENTE -->
        <div class="fecha-edicion-devolucion" style="display:none; margin-top:8px;">
          <input type="date" id="nuevaFechaDevolucion">
          <input type="time" id="nuevaHoraDevolucion">
          <button type="button" class="btn small" id="btnGuardarFechaDevolucion">Guardar</button>
        </div>

        <div class="hora">{{ \Carbon\Carbon::parse($reservacion->hora_entrega)->format('h:i A') }}</div>
      </div>
    </div>




    <!-- 🔹 Datos del cliente -->
    <div class="card resumen-totales">
      <div class="kv"><div>Teléfono</div><div id="clienteTel">{{ $reservacion->telefono_cliente }}</div></div>
      <div class="kv"><div>Correo electrónico</div><div id="clienteEmail">{{ $reservacion->email_cliente }}</div></div>
      <div class="kv"><div>Duración</div>
        <div id="diasBadge">
          {{ \Carbon\Carbon::parse($reservacion->fecha_inicio)->diffInDays($reservacion->fecha_fin)}} días
        </div>
      </div>

    <!--====================================================================================-->
 <div class="kv total">
        <div style="font-weight:bold;color:#d00;">Total reservado</div>
        <div class="total" id="totalReserva" style="font-weight:bold;color:#d00;">
          ${{ number_format($reservacion->total, 2) }} MXN
        </div>
      </div>
    </div>
    <!---=======================================================================================-->
    <!-- 🔹 SELECT DE CATEGORÍA -->
    <div class="card" style="margin-top:20px;">
      <label style="font-weight:bold;">Categoría reservada</label>
      <select id="selectCategoria" class="input" style="width:100%; margin-top:8px;">
        @foreach($categorias as $cat)
          <option value="{{ $cat->id_categoria }}"
            {{ $reservacion->id_categoria == $cat->id_categoria ? 'selected' : '' }}>
            {{ $cat->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <!-- 🔹 BOTÓN ELEGIR VEHÍCULO -->
    <div style="margin-top:15px; text-align:left;">
      <button type="button" class="btn secondary" id="btnElegirVehiculo">
        🚗 Elegir vehículo
      </button>
    </div>

    <!-- 🔹 Botón -->
    <div class="acciones" style="margin-top:20px;text-align:right;">
      <button class="btn primary" id="go2" type="button">✅ Continuar</button>
    </div>

  </div>
</article>


<!-- ======================
     PASO 2 · SERVICIOS ADICIONALES
======================= -->
<article class="step" data-step="2">
  <header>
    <div class="badge">2</div>
    <h3>PASO 2 · Servicios adicionales</h3>
  </header>

  <div class="body">
    <section class="section">
      <div class="head">Selecciona servicios adicionales</div>

      <div class="cnt">
        <!-- 🔹 GRID DINÁMICO DE SERVICIOS -->
        <div id="serviciosGrid" class="add-grid">
          @forelse ($servicios as $s)
            @php
              $rel = DB::table('reservacion_servicio')
                ->where('id_reservacion', $reservacion->id_reservacion ?? 0)
                ->where('id_servicio', $s->id_servicio)
                ->first();
              $cantidad = $rel->cantidad ?? 0;
            @endphp

            <div class="card-servicio"
                data-id="{{ $s->id_servicio }}"
                data-precio="{{ $s->precio }}"
                data-tipo="{{ $s->tipo_cobro }}"
                data-nombre="{{ $s->nombre }}">

              <h4>{{ $s->nombre }}</h4>

              @if($s->descripcion)
                <p>{{ $s->descripcion }}</p>
              @endif

              <div class="precio">
                <strong>${{ number_format($s->precio, 2) }} MXN/día</strong>
              </div>

              <div class="contador">
                <button class="menos">−</button>
                <span class="cantidad">{{ $cantidad }}</span>
                <button class="mas">+</button>
              </div>
            </div>
          @empty
            <p>No hay servicios adicionales disponibles.</p>
          @endforelse
        </div>


       <!-- ============================================
     🚚 BLOQUE DE DELIVERY (VERSIÓN FINAL CORREGIDA)
============================================ -->
<div class="delivery-wrapper" style="margin-top:25px;"
     data-id-reservacion="{{ $reservacion->id_reservacion }}"
     data-delivery-activo="{{ $delivery->activo ?? 0 }}"
     data-delivery-km="{{ $delivery->kms ?? '' }}"
     data-delivery-direccion="{{ $delivery->direccion ?? '' }}"
     data-delivery-total="{{ $delivery->total ?? 0 }}"
     data-delivery-ubicacion="{{ isset($delivery->id_ubicacion) ? $delivery->id_ubicacion : '' }}"
     data-costo-km="{{ $costoKmCategoria }}">

    <div class="head" style="margin-bottom:10px;">
        Delivery
    </div>

    <!-- SWITCH -->
    <label class="switch">
        <input type="checkbox"
               id="deliveryToggle"
               name="delivery_activo"
               {{ !empty($delivery->activo) ? 'checked' : '' }}>
        <span class="slider"></span>
    </label>

    <!-- CAMPOS QUE SE MUESTRAN AL ACTIVAR -->
    <div id="deliveryFields"
         style="display: {{ !empty($delivery->activo) ? 'block' : 'none' }}; margin-top:20px;">

        <!-- SELECT UBICACIÓN -->
        <div class="form-group">
            <label>Seleccionar ubicación</label>
            <select id="deliveryUbicacion"
                    name="delivery_ubicacion"
                    class="form-control">

                <option value="">Seleccione...</option>

                @foreach($ubicaciones as $u)
                    <option value="{{ $u->id_ubicacion }}"
                            data-km="{{ $u->km }}"
                            {{ (!empty($delivery->id_ubicacion) && $delivery->id_ubicacion == $u->id_ubicacion) ? 'selected' : '' }}>
                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km }} km)
                    </option>
                @endforeach

                <!-- OPCIÓN PERSONALIZADA -->
                <option value="0"
                        {{ (isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0) ? 'selected' : '' }}>
                    Dirección personalizada (manual)
                </option>

            </select>
        </div>

        <!-- DIRECCIÓN PERSONALIZADA (solo si personalizada) -->
        <div id="groupDireccion"
             class="form-group"
             style="margin-top:15px;
                    display: {{ (isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0) ? 'block' : 'none' }};">

            <label>Dirección personalizada (opcional)</label>
            <input type="text"
                   id="deliveryDireccion"
                   name="delivery_direccion"
                   class="form-control"
                   placeholder="Ej. Calle Robles 123, Centro"
                   value="{{ $delivery->direccion ?? '' }}">
        </div>

        <!-- KM PERSONALIZADOS (solo si personalizada) -->
        <div id="groupKm"
             class="form-group"
             style="margin-top:15px;
                    display: {{ (isset($delivery->id_ubicacion) && $delivery->id_ubicacion == 0) ? 'block' : 'none' }};">

            <label>Kilómetros personalizados</label>
            <input type="number"
                   min="0"
                   id="deliveryKm"
                   name="delivery_km"
                   class="form-control"
                   placeholder="Ej. 15"
                   value="{{ $delivery->kms ?? '' }}">
        </div>

        <!-- TOTAL DELIVERY -->
        <div style="margin-top:15px; font-weight:bold;">
            Total Delivery:
            <span id="deliveryTotal">
                ${{ number_format($delivery->total ?? 0, 2) }} MXN
            </span>
        </div>

    </div>

    <!-- Controles internos usados por JS -->
    <input type="hidden" id="deliveryPrecioKm" value="{{ $costoKmCategoria }}">
    <input type="hidden" id="deliveryTotalHidden" value="{{ $delivery->total ?? 0 }}">

</div>
<!-- ============================================
     FIN BLOQUE DELIVERY
============================================ -->


        <!-- 🔹 Total parcial -->
        <div class="totalBox" style="margin-top:20px;">
          <div class="kv">
            <div>Total adicionales</div>
            <div class="total" id="total_servicios">$0.00 MXN</div>
          </div>
        </div>

        <!-- 🔹 Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back1" type="button">← Atrás</button>
          <button class="btn primary" id="go3" type="button">Continuar →</button>
        </div>

      </div>
    </section>
  </div>
</article>




<!-- ======================
     PASO 3 · PROTECCIONES
======================= -->
<article class="step" data-step="3">
  <header>
    <div class="badge">3</div>
    <h3>PASO 3 · Protecciones del contrato</h3>
  </header>

  <div class="body">
    <section class="section">
      <div class="head">Selecciona un paquete o protecciones individuales</div>

      <div class="cnt">

        <!-- 🟢 Nota informativa -->
        <div class="note">
          Si eliges un <b>paquete</b>, se desactivan las individuales.
          Si activas alguna <b>individual</b>, se desmarca el paquete.
        </div>

        <!-- 🔘 BOTONES PRINCIPALES -->
        <div style="display:flex; gap:12px; margin:15px 0;">
          <button type="button" class="btn primary" id="btnVerPaquetes">
              Ver paquetes de seguro
          </button>

          <button type="button" class="btn gray" id="btnVerIndividuales">
              Armar mi paquete
          </button>
        </div>

        <!-- 💰 Total visual -->
        <div class="totalBox" style="margin-top:18px;">
          <div class="kv">
            <div>Total protecciones</div>
            <div class="total" id="total_seguros">
              ${{ isset($seguroSeleccionado) ? number_format($seguroSeleccionado->precio_por_dia, 2) : '0.00' }} MXN
            </div>
          </div>
        </div>

        <!-- 🧭 Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back2" type="button">← Atrás</button>
          <button class="btn primary" id="go4" type="button" {{ empty($seguroSeleccionado) ? 'disabled' : '' }}>
            Continuar →
          </button>
        </div>

      </div>

    </section>
  </div>
</article>

<!-- ======================
     PASO 4 · CONFIGURACIÓN FINAL
======================= -->
<article class="step" data-step="4">
  <header>
    <div class="badge">4</div>
    <h3>PASO 4 · Configuración final</h3>
  </header>

  <div class="body">
    <section class="section">
      <div class="head">Ajusta asignación y cargos opcionales</div>
      <div class="cnt">

        <!-- 🗓️ Itinerario -->
        <div class="card">
          <div class="head">
            <div class="hTitle"><div class="hIcon">🗓️</div> Itinerario programado</div>
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


        <!-- 🚗 Vehículo asignado -->
        <div class="card">
          <div class="head">
            <div class="hTitle"><div class="hIcon">🚗</div> Cambio de vehículo</div>

            <button id="editVeh"
                    class="btn"
                    style="background:#fff;border:1px solid var(--stroke);">
              ✏️ Editar
            </button>
          </div>

          <div class="body">
            <div class="kvline">
              <div class="k">Unidad</div>
              <div>

                <select id="vehAssign" disabled>
                  @if($vehiculo)
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


        <!-- ============================
             GASOLINA FALTANTE
        ============================= -->
        <div class="card">
          <div class="head">
            <div class="hTitle">
              <div class="hIcon">⛽</div> Gasolina faltante
            </div>
          </div>

          <div class="body">

            <div class="cargo-item" data-tipo="litros-gasolina">
              <div class="head">
                <div class="hTitle"><div class="hIcon">🛢️</div> Litros faltantes</div>

                <!-- GASOLINA = id_concepto 5 -->
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



        <!-- ============================
             DROPOFF
        ============================= -->
        <div class="card">
          <div class="head">
            <div class="hTitle"><div class="hIcon">📍</div> Dropoff</div>
          </div>

          <div class="body">
            <div class="note">Selecciona la ubicación donde el cliente devolverá el vehículo.</div>

            <!-- DROPOFF = id_concepto 6 -->
            <div class="switch" id="switchDropoff" data-idconcepto="6"></div>

            <div id="dropoffFields" style="display:none;margin-top:15px;">

              <div class="form-group">
                <label>Seleccionar ubicación</label>
                <select id="dropUbicacion" class="form-control">
                  <option value="">Seleccione...</option>

                  @foreach($ubicaciones as $u)
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
          </div>
        </div>




        <!-- ============================
             OTROS CARGOS ADICIONALES
        ============================= -->
        <div class="card">
          <div class="head">
            <div class="hTitle"><div class="hIcon">💰</div> Otros cargos adicionales</div>
          </div>

          <div class="body">

            <div class="note">Activa solo los cargos que correspondan.</div>

            <div id="cargosGrid" class="add-grid">

              @foreach($cargos_conceptos as $cargo)
                @php
                  $activo = DB::table('cargo_adicional')
                      ->where('id_contrato', $contrato->id_contrato ?? 0)
                      ->where('id_concepto', $cargo->id_concepto)
                      ->exists();

                  // NO REPETIR dropoff (6) ni gasolina (5)
                  if ($cargo->id_concepto == 5 || $cargo->id_concepto == 6) continue;
                @endphp

                <div class="card cargo-item"
                     data-id="{{ $cargo->id_concepto }}"
                     data-nombre="{{ $cargo->nombre }}"
                     data-monto="{{ $cargo->monto_base ?? 0 }}">

                  <div class="head">
                    <div class="hTitle"><div class="hIcon">🧾</div> {{ $cargo->nombre }}</div>
                    <div class="switch {{ $activo ? 'on':'' }}"
                         data-id="{{ $cargo->id_concepto }}"></div>
                  </div>

                  <div class="body">
                    @if($cargo->descripcion)
                      <p>{{ $cargo->descripcion }}</p>
                    @endif

                    <div class="precio">${{ number_format($cargo->monto_base, 2) }} {{ $cargo->moneda }}</div>
                  </div>

                </div>

              @endforeach

            </div>

            <!-- Total -->
            <div class="totalBox" style="margin-top:18px;">
              <div class="kv">
                <div>Total cargos</div>
                <div class="total" id="total_cargos">
                  ${{ number_format(
                    DB::table('cargo_adicional')
                      ->where('id_contrato', $contrato->id_contrato ?? 0)
                      ->sum('monto'), 2
                  ) }} MXN
                </div>
              </div>
            </div>

          </div>
        </div>


        <!-- Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back3" type="button">← Atrás</button>
          <button class="btn primary" id="go5" type="button">Continuar →</button>
        </div>

      </div>
    </section>
  </div>
</article>

{!! $modalVehiculos ?? '' !!}





<!-- ======================
     PASO 5 · DOCUMENTACIÓN (MODO SIMPLE)
======================= -->
<article class="step" data-step="5">
  <header>
    <div class="badge">5</div>
    <h3>PASO 5 · Documentación (modo simple)</h3>
  </header>

  <div class="body">
    <!-- 📄 FORMULARIO PRINCIPAL -->
    <form id="formDocumentacion"
          action="{{ route('contrato.guardarDocumentacion') }}"
          method="POST"
          enctype="multipart/form-data"
          data-adicionales="{{ count($conductoresExtras ?? []) }}"
          data-actual="0"
          data-conductores='@json($conductoresExtras ?? [])'
          data-titular="{{ $contrato->nombre_titular ?? 'Titular' }}">
      @csrf

      <!-- Hidden Inputs -->
      <input type="hidden" id="id_contrato" name="id_contrato" value="{{ $contrato->id_contrato }}">
      <input type="hidden" id="id_conductor" name="id_conductor" value="">
      <input type="hidden" id="conductor_index" name="conductor_index" value="0">
      <input type="hidden" id="total_conductores" value="{{ count($conductoresExtras ?? []) }}">

      <!-- ======================
           BLOQUE DE DOCUMENTACIÓN
      ======================= -->
      <section class="section" id="bloque-documentacion">
        <div class="head">
          <span id="tituloPersona">Documentación del Titular</span>
        </div>

        <!-- 🔹 Identificación Oficial -->
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

            <!-- ⭐ NUEVO CAMPO (opcional) -->
            <div class="input-row">
              <label>Contacto de Emergencia</label>
              <input id="contactoEmergencia" name="contacto_emergencia" type="text" placeholder="Nombre y teléfono" autocomplete="off">
            </div>
            <!-- ⭐ -->

            <div class="input-row">
              <label>Fecha de Nacimiento</label>
              <input id="idNacimiento" name="fecha_nacimiento" type="date" required>
            </div>

            <div class="input-row">
              <label>Fecha de Vencimiento del ID</label>
              <input id="idVence" name="fecha_vencimiento_id" type="date" required>
            </div>

          </div>

          <!-- 🖼️ Subida de imágenes INE -->
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

      <!-- ======================
           LICENCIA DE CONDUCIR
      ======================= -->
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

          <!-- ⚠️ Advertencias -->
          <div id="alertaLicencia" class="pill-warn" style="margin-top:8px; display:none;">
            ⚠️ Licencia vencida: por favor sube una licencia vigente para continuar.
          </div>

          <div id="confirmacionLicencia" class="pill-ok" style="margin-top:8px; display:none;">
            ✅ Licencia vigente verificada correctamente.
          </div>

        </div>
      </section>

      <!-- Navegación -->
      <div class="acciones" style="margin-top:20px;">
        <button class="btn gray" id="back4" type="button">← Atrás</button>
        <button class="btn primary" id="btnContinuarDoc" type="submit">Guardar y Continuar →</button>
        <button class="btn success" id="btnSaltarDoc" type="button" style="margin-left:8px;">
  Continuar sin volver a subir →
</button>

        <div class="small" style="margin-top:8px;">
          Se guarda automáticamente. Requisitos: fotos de frente y reverso de INE y Licencia.
        </div>
      </div>

    </form>

    <!-- 📦 Bloque de conductores adicionales -->
    <div id="bloquesConductores" style="display:none;"></div>

  </div>
</article>



<!-- ======================
     PASO 6 · ESTADO DE CUENTA Y PAGOS
======================= -->
<article class="step" data-step="6">
  <header>
    <div class="badge">6</div>
    <h3>PASO 6 · Estado de cuenta y pagos</h3>
  </header>

  <div class="body">

    <!-- 🔹 ANTES SE LLAMABA "Resumen" -->
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
            <tr>
              <td colspan="6" style="text-align:center;color:#667085">
                NO EXISTEN PAGOS REGISTRADOS
              </td>
            </tr>
          </tbody>
        </table>

        <div class="right" style="margin-top:10px">
          <button id="btnAdd" class="btn primary">REGISTRAR PAGO</button>
        </div>
      </div>
    </section>

    <!-- 🔹 Navegación -->
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



    <!-- ======================
     RESUMEN CONTRATO (COLAPSABLE)
====================== -->
<aside class="sticky">
  <div class="card resumen-card">

    <!-- CABECERA -->
    <div class="head">Resumen del Contrato</div>

    <!-- ==============================
         🔹 MODO COMPACTO
    =============================== -->
    <div class="cnt resumen-compacto" id="resumenCompacto">

      <!-- IMAGEN + INFO BÁSICA -->
      <div id="vehiculo_info" class="vehiculo-mini-wrap">
        <img id="resumenImgVeh"
             src="{{ asset('img/default-car.png') }}"
             alt="Vehículo"
             class="vehiculo-img">

        <p class="vehiculo-nombre" id="resumenVehCompacto">—</p>
        <p class="vehiculo-mini" id="resumenCategoriaCompacto">Categoría: —</p>
        <p class="vehiculo-mini" id="resumenDiasCompacto">Días de renta: —</p>
        <p class="vehiculo-mini" id="resumenFechasCompacto">— / —</p>
      </div>

      <!-- TOTAL -->
      <div class="totalBox" style="margin-top:12px;">
        <div class="kv">
          <div>Total actual</div>
          <div class="total" id="resumenTotalCompacto">$0.00 MXN</div>
        </div>
      </div>

      <!-- BOTÓN EXPANDIR -->
      <button id="btnVerDetalle" class="btn-resumen">
        Ver detalle ▼
      </button>
    </div>

    <!-- ==============================
         🔹 MODO DETALLE (EXPANDIDO)
    =============================== -->
    <div class="cnt resumen-detalle" id="resumenDetalle" style="display:none;">

      <div id="detalleContenido">

        <!-- ======================
             1) DATOS GENERALES
        ======================= -->
        <section class="res-block">
          <h4>Código de reservación</h4>
          <p id="detCodigo">—</p>
        </section>

        <section class="res-block">
          <h4>Datos del cliente</h4>
          <p id="detCliente">—</p>
          <p id="detTelefono">—</p>
          <p id="detEmail">—</p>
        </section>

        <!-- ======================
             2) VEHÍCULO
        ======================= -->
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

        <!-- ======================
             3) FECHAS
        ======================= -->
        <section class="res-block">
          <h4>Fechas y horarios</h4>
          <p>Salida: <span id="detFechaSalida">—</span> · <span id="detHoraSalida">—</span></p>
          <p>Entrega: <span id="detFechaEntrega">—</span> · <span id="detHoraEntrega">—</span></p>
          <p>Días totales: <span id="detDiasRenta">—</span></p>
        </section>

        <!-- ======================
             4) PAQUETES DE COBERTURA
        ======================= -->
        <section class="res-block">
          <h4>Paquetes de cobertura</h4>
          <ul id="r_seguros_lista" class="det-lista">
            <li class="empty">—</li>
          </ul>
          <p>Total: <b id="r_seguros_total">—</b></p>
        </section>

        <!-- ======================
             5) ADICIONALES
        ======================= -->
        <section class="res-block">
          <h4>Adicionales</h4>
          <ul id="r_servicios_lista" class="det-lista">
            <li class="empty">—</li>
          </ul>
          <p>Total: <b id="r_servicios_total">—</b></p>
        </section>

        <!-- ======================
             6) CARGOS / GASOLINA / DROPOFF
        ======================= -->
        <section class="res-block">
          <h4>Servicios adicionales</h4>
          <ul id="r_cargos_lista" class="det-lista">
            <li class="empty">—</li>
          </ul>
        </section>

        <!-- ======================
             7) TOTAL DESGLOSADO
        ======================= -->
        <section class="res-block">
          <h4>Total desglosado</h4>

          <!-- Tarifa base editable -->
          <p>
            Tarifa base:
            <b id="r_base_precio">—</b>
            <button id="btnEditarTarifa"
                    style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">
              ✏️
            </button>
          </p>

          <!-- Horas de cortesía editable -->
          <p>
            Horas de cortesía:
            <span id="r_cortesia">1</span>
            <button id="btnEditarCortesia"
                    style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:15px;margin-left:6px;">
              ✏️
            </button>
          </p>

          <!-- Editor oculto -->
          <div id="editorCortesia" style="display:none; margin-top:6px;">
            <select id="inputCortesia" style="padding:4px;border-radius:6px;border:1px solid #ccc;">
              <option value="1">1 hora</option>
              <option value="2">2 horas</option>
              <option value="3">3 horas</option>
            </select>

            <button id="btnGuardarCortesia"
                    style="margin-left:8px;background:#2563eb;color:white;border:none;padding:4px 8px;border-radius:6px;cursor:pointer;">
              Guardar
            </button>

            <button id="btnCancelarCortesia"
                    style="margin-left:4px;background:#ccc;border:none;padding:4px 8px;border-radius:6px;cursor:pointer;">
              Cancelar
            </button>
          </div>

          <p>Subtotal: <b id="r_subtotal">—</b></p>
          <p>IVA: <b id="r_iva">—</b></p>
          <p>Total contrato: <b id="r_total_final">—</b></p>
        </section>

        <!-- ======================
             8) PAGOS REALIZADOS
        ======================= -->
        <section class="res-block">
          <h4>Pagos y saldo</h4>
          <p>Pagos realizados: <b id="detPagos">—</b></p>
          <p>Saldo pendiente: <b id="detSaldo">—</b></p>
        </section>

      </div>

      <!-- BOTÓN OCULTAR -->
      <button id="btnOcultarDetalle" class="btn-resumen">
        Ocultar detalle ▲
      </button>
    </div>

  </div>
</aside>






  <!-- ======================
     MODAL · REGISTRAR PAGO (REMODELADO)
======================= -->
<div class="modal-back" id="mb">
  <div class="modal modal-pagos">

    <!-- CABECERA -->
    <div class="head">
      Registrar Pago
      <button id="mx" class="btn gray" style="padding:6px 10px">✕</button>
    </div>

    <!-- CUERPO -->
    <div class="body">

      <!-- TABS -->
      <div class="pay-groups" id="payTabs">
        <button class="tab active" data-tab="paypal">PayPal</button>
        <button class="tab" data-tab="tarjeta">Terminal</button>
        <button class="tab" data-tab="efectivo">Efectivo</button>
        <button class="tab" data-tab="transferencia">Transferencia / Depósito</button>
        <button class="tab disabled" disabled style="opacity:0.4;">Cripto</button>
      </div>

      <!-- PANELES -->
      <div id="methods">

        <!-- 🔵 PAYPAL -->
        <div data-pane="paypal">
          <p class="small">
            Al seleccionar PayPal, se abrirá la pasarela en línea.
            El pago se confirmará automáticamente y se generará un recibo interno.
          </p>

          <!-- NUEVO CONTENEDOR PAYPAL (diseño elegante y centrado) -->
          <div class="paypal-box">
            <div id="paypal-button-container-modal"></div>
          </div>
        </div>

        <!-- 🟣 TARJETA FÍSICA / TERMINAL -->
        <div data-pane="tarjeta" style="display:none;">
          <div class="method-grid">
            <label class="mcard">
              <input type="radio" name="m" value="VISA">
              <img src="../assets/media/visa.png" alt="">
              <div><div class="ttl">VISA</div><div class="sub">Terminal</div></div>
            </label>

            <label class="mcard">
              <input type="radio" name="m" value="MASTERCARD">
              <img src="../assets/media/master.jpg" alt="">
              <div><div class="ttl">Mastercard</div><div class="sub">Terminal</div></div>
            </label>

            <label class="mcard">
              <input type="radio" name="m" value="AMEX">
              <img src="../assets/media/amex.png" alt="">
              <div><div class="ttl">AMEX</div><div class="sub">Terminal</div></div>
            </label>

            <label class="mcard">
              <input type="radio" name="m" value="DEBITO">
              <img src="../assets/media/debito.png" alt="">
              <div><div class="ttl">Débito</div><div class="sub">Terminal</div></div>
            </label>
          </div>

          <!-- Ticket obligatorio -->
          <div style="margin-top:15px;">
            <label>Foto del ticket (obligatorio)</label>
            <input id="fileTerminal" type="file" accept="image/*,.pdf">
          </div>
        </div>

        <!-- 🟢 EFECTIVO -->
        <div data-pane="efectivo" style="display:none;">
          <p class="small">
            Se generará automáticamente un ticket interno y el pago se marcará como completado.
          </p>
        </div>

        <!-- 🟠 TRANSFERENCIA / DEPOSITO -->
        <div data-pane="transferencia" style="display:none;">
          <div class="method-grid">
            <label class="mcard">
              <input type="radio" name="m" value="TRANSFERENCIA">
              <img src="../assets/media/transfe.jpg" alt="">
              <div><div class="ttl">Transferencia</div><div class="sub">Cuenta</div></div>
            </label>

            <label class="mcard">
              <input type="radio" name="m" value="SPEI">
              <img src="../assets/media/spei.png" alt="">
              <div><div class="ttl">SPEI</div><div class="sub">MX</div></div>
            </label>

            <label class="mcard">
              <input type="radio" name="m" value="DEPOSITO">
              <img src="../assets/media/deposito.png" alt="">
              <div><div class="ttl">Depósito</div><div class="sub">Sucursal</div></div>
            </label>
          </div>

          <!-- Comprobante obligatorio -->
          <div style="margin-top:15px;">
            <label>Comprobante del pago (obligatorio)</label>
            <input id="fileTransfer" type="file" accept="image/*,.pdf">
          </div>
        </div>

      </div>

      <!-- DATOS GENERALES DEL PAGO -->
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

            <!-- ESTE INPUT SE LLENARÁ AUTOMÁTICAMENTE DESDE JS -->
            <input id="pMonto" type="number" step="0.01" min="0" placeholder="0.00">

            <div class="err" id="pErr"></div>
          </div>

          <div style="grid-column:1/-1;">
            <label>Notas (opcional)</label>
            <textarea id="pNotes" rows="2" placeholder="Referencia, banco, comentario..."></textarea>
          </div>

        </div>
      </fieldset>

    </div>

    <!-- FOOTER -->
    <div class="foot">
      <button id="pSave" class="btn primary">GUARDAR PAGO</button>
    </div>

  </div>
</div>



<!-- ============================================================
     🚗 MODAL: ELEGIR VEHÍCULO (ESTILO PROFESIONAL)
============================================================ -->
<div id="modalVehiculos" class="modal-vehiculos">
  <div class="modal-content">

    <!-- 🔴 HEADER -->
    <div class="modal-header">
      <span>Vehículos disponibles</span>
      <button type="button" id="cerrarModalVehiculos" class="close-btn">✕</button>
    </div>

    <!-- 🔽 SELECT DE CATEGORÍA (NUEVO) -->
    <div class="modal-select-categoria" style="margin: 15px 0;">
      <label style="font-weight:600; font-size:14px;">Filtrar por categoría</label>
      <select id="selectCategoriaModal" class="filtro-input" style="margin-top:6px;">
        @foreach($categorias as $cat)
          <option value="{{ $cat->id_categoria }}">
            {{ $cat->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <!-- 🧰 FILTROS -->
    <div class="modal-filtros">
      <div class="filtros-grid">
        <input type="text" id="filtroColor" placeholder="Color" class="filtro-input">
        <input type="text" id="filtroModelo" placeholder="Modelo" class="filtro-input">
        <input type="text" id="filtroSerie" placeholder="Número de serie (VIN)" class="filtro-input">
      </div>
    </div>

    <!-- 📜 LISTA VEHÍCULOS -->
    <div id="listaVehiculos" class="modal-lista"></div>

    <!-- 🔘 FOOTER -->
    <div class="modal-footer">
      <button id="cerrarModalVehiculos2" class="btn-cerrar">Cerrar</button>
    </div>

  </div>
</div>

<!-- ============================================================
     🚀 MODAL DE UPGRADE DE CATEGORÍA — ESTILO PREMIUM
============================================================ -->
<div id="modalUpgrade" class="upgrade-modal">

    <div class="upgrade-card">

        <!-- ❌ Botón para cerrar -->
        <button class="upgrade-close" id="cerrarUpgrade">✕</button>

        <!-- 🔥 Burbuja de descuento -->
        <div class="upgrade-discount-badge">
            <span id="upgDescuento"></span>
        </div>

        <!-- 🚗 Imagen del vehículo -->
        <div class="upgrade-image-wrapper">
            <img id="upgImagenVehiculo" src="" alt="Vehículo upgrade">
        </div>

        <!-- 🟥 Categoría -->
        <h3 class="upgrade-categoria" id="upgTitulo"></h3>

        <!-- 🟦 Nombre completo del vehículo -->
        <h4 class="upgrade-nombre-vehiculo" id="upgNombreVehiculo"></h4>

        <!-- 📝 Descripción corta -->
        <p class="upgrade-descripcion" id="upgDescripcion"></p>



        <!-- ⭐ BENEFICIOS -->
        <div class="upgrade-beneficios" id="upgBeneficios">
            <!-- Se llena desde JS si deseas -->
        </div>

        <!-- ⭐ ESPECIFICACIONES EXTRA (REQUIRED POR TU JS) -->
        <!-- ⚠️ Este contenedor LO REQUIERE tu función mostrarModalOferta -->
        <!-- Si no existe → ERROR -->
        <div id="upgSpecs" style="margin-top:15px;"></div>

        <!-- 💵 Precios -->
        <div class="upgrade-precios">
            <span class="upgrade-precio-inflado" id="upgPrecioInflado"></span>
            <span class="upgrade-precio-real" id="upgPrecioReal"></span>
        </div>

        <!-- 🔘 Botones -->
        <div class="upgrade-buttons">
            <button id="btnRechazarUpgrade" class="btn-upgrade-cancel">No gracias</button>
            <button id="btnAceptarUpgrade" class="btn-upgrade-accept">Aceptar upgrade</button>
        </div>

    </div>
</div>

<!-- ======================
     MODAL · PAQUETES
======================= -->
<div id="modalPaquetes" class="modal" style="display:none;">
  <div class="modal-content modal-large">

    <!-- ENCABEZADO -->
    <div class="modal-header">
      <h2>Paquetes de Seguro</h2>
     <button type="button" class="close-modal" data-target="paquetes">&times;</button>
    </div>

    <!-- CUERPO DEL MODAL -->
    <div class="modal-body">

      <h3 style="margin:8px 0 14px;">Paquetes (precio por día)</h3>

      <div id="packGrid" class="cards">

        @foreach($seguros as $seguro)
        <label class="card seguro-item"
               data-id="{{ $seguro->id_seguro }}"
               data-precio="{{ $seguro->precio_por_dia }}">

          <div class="body">
            <h4>{{ $seguro->nombre }}</h4>
            <p>{{ $seguro->cobertura }}</p>

            <div class="precio">
              ${{ number_format($seguro->precio_por_dia, 2) }} MXN x Día
            </div>

            <!-- FIX BLADE: switch en UNA sola línea -->
            <div class="switch {{ $seguro->id_seguro == ($seguroSeleccionado->id_seguro ?? null) ? 'on' : '' }}"
                 data-id="{{ $seguro->id_seguro }}">
            </div>

            <div class="small" style="margin-top:8px;">Seleccionar Paquete</div>
          </div>

        </label>
        @endforeach

      </div>

    </div>

  </div>
</div>


<!-- ======================================================
      MODAL — ARMAR PAQUETE (Protecciones Individuales)
====================================================== -->
<div class="modal" id="modalIndividuales" style="display:none;">
  <div class="modal-content">

    <header class="modal-header">
      <h3>Protecciones individuales</h3>
      <button class="closeModal" data-target="individuales">&times;</button>
    </header>

    <section class="modal-body">

      <div class="note" style="margin-bottom:14px;">
        Selecciona una o varias protecciones individuales.
      </div>

      <!-- ================================
           COLISIÓN Y ROBO
      ================================= -->
      <h4 class="categoria-title">Colisión y robo</h4>
      <div class="cards scroll-h">
        @foreach($grupo_colision as $ind)
        <label class="card individual-item"
               data-id="{{ $ind->id_individual }}"
               data-precio="{{ $ind->precio_por_dia }}">

          <div class="body">
            <h4>{{ $ind->nombre }}</h4>
            <p>{{ $ind->descripcion }}</p>

            <div class="precio">
              ${{ number_format($ind->precio_por_dia, 2) }} MXN x Día
            </div>

            <div class="switch switch-individual"
                 data-id="{{ $ind->id_individual }}">
            </div>

            <div class="small">Incluir</div>
          </div>

        </label>
        @endforeach
      </div>

      <!-- ================================
           GASTOS MÉDICOS
      ================================= -->
      <h4 class="categoria-title">Gastos médicos</h4>
      <div class="cards scroll-h">
        @foreach($grupo_medicos as $ind)
        <label class="card individual-item"
               data-id="{{ $ind->id_individual }}"
               data-precio="{{ $ind->precio_por_dia }}">

          <div class="body">
            <h4>{{ $ind->nombre }}</h4>
            <p>{{ $ind->descripcion }}</p>

            <div class="precio">
              ${{ number_format($ind->precio_por_dia, 2) }} MXN x Día
            </div>

            <div class="switch switch-individual"
                 data-id="{{ $ind->id_individual }}">
            </div>

            <div class="small">Incluir</div>
          </div>

        </label>
        @endforeach
      </div>

      <!-- ================================
           ASISTENCIA PARA EL CAMINO
      ================================= -->
      <h4 class="categoria-title">Asistencia para el camino</h4>
      <div class="cards scroll-h">
        @foreach($grupo_asistencia as $ind)
        <label class="card individual-item"
               data-id="{{ $ind->id_individual }}"
               data-precio="{{ $ind->precio_por_dia }}">

          <div class="body">
            <h4>{{ $ind->nombre }}</h4>
            <p>{{ $ind->descripcion }}</p>

            <div class="precio">
              ${{ number_format($ind->precio_por_dia, 2) }} MXN x Día
            </div>

            <div class="switch switch-individual"
                 data-id="{{ $ind->id_individual }}">
            </div>

            <div class="small">Incluir</div>
          </div>

        </label>
        @endforeach
      </div>

      <!-- ================================
           DAÑOS A TERCEROS
      ================================= -->
      <h4 class="categoria-title">Daños a terceros</h4>
      <div class="cards scroll-h">
        @foreach($grupo_terceros as $ind)
        <label class="card individual-item"
               data-id="{{ $ind->id_individual }}"
               data-precio="{{ $ind->precio_por_dia }}">

          <div class="body">
            <h4>{{ $ind->nombre }}</h4>
            <p>{{ $ind->descripcion }}</p>

            <div class="precio">
              ${{ number_format($ind->precio_por_dia, 2) }} MXN x Día
            </div>

            <div class="switch switch-individual"
                 data-id="{{ $ind->id_individual }}">
            </div>

            <div class="small">Incluir</div>
          </div>

        </label>
        @endforeach
      </div>

      <!-- ================================
           PROTECCIONES AUTOMÁTICAS
      ================================= -->
      <h4 class="categoria-title">Protecciones automáticas</h4>
      <div class="cards scroll-h">
        @foreach($grupo_protecciones as $ind)
        <label class="card individual-item"
               data-id="{{ $ind->id_individual }}"
               data-precio="{{ $ind->precio_por_dia }}">

          <div class="body">
            <h4>{{ $ind->nombre }}</h4>
            <p>{{ $ind->descripcion }}</p>

            <div class="precio">
              ${{ number_format($ind->precio_por_dia, 2) }} MXN x Día
            </div>

            <div class="switch switch-individual"
                 data-id="{{ $ind->id_individual }}">
            </div>

            <div class="small">Incluir</div>
          </div>

        </label>
        @endforeach
      </div>

    </section>

  </div>
</div>

</main>
@endsection
@section('js-vistaContrato')

<script>
     window.contratoId = {{ $contrato->id_contrato }};
    window.clienteContratoUrl = "{{ route('contrato.obtenerCliente', $contrato->id_contrato) }}";
</script>

<script src="{{ asset('js/Contrato.js') }}"></script>
@endsection


