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
      <div class="bloque entrega">
        <div class="titulo">ENTREGA</div>
        <p class="lugar">Sucursal de origen</p>
        <div class="fecha">
          <div class="dia">{{ \Carbon\Carbon::parse($reservacion->fecha_inicio)->format('d') }}</div>
          <div class="mes">{{ strtoupper(\Carbon\Carbon::parse($reservacion->fecha_inicio)->format('M')) }}</div>
          <div class="anio">{{ \Carbon\Carbon::parse($reservacion->fecha_inicio)->format('Y') }}</div>
        </div>
        <div class="hora">{{ \Carbon\Carbon::parse($reservacion->hora_retiro)->format('h:i A') }}</div>
      </div>

      <div class="bloque devolucion">
        <div class="titulo">DEVOLUCIÓN</div>
        <p class="lugar">Sucursal destino</p>
        <div class="fecha">
          <div class="dia">{{ \Carbon\Carbon::parse($reservacion->fecha_fin)->format('d') }}</div>
          <div class="mes">{{ strtoupper(\Carbon\Carbon::parse($reservacion->fecha_fin)->format('M')) }}</div>
          <div class="anio">{{ \Carbon\Carbon::parse($reservacion->fecha_fin)->format('Y') }}</div>
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
          {{ \Carbon\Carbon::parse($reservacion->fecha_inicio)->diffInDays($reservacion->fecha_fin) + 1 }} días
        </div>
      </div>
      <div class="kv total">
        <div style="font-weight:bold;color:#d00;">Total reservado</div>
        <div class="total" id="totalReserva" style="font-weight:bold;color:#d00;">
          ${{ number_format($reservacion->total, 2) }} MXN
        </div>
      </div>
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
              // Buscar si este servicio ya está asociado a la reservación
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

              <!-- Contador visual -->
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

        <!-- 🔹 Título de los paquetes -->
        <h3 style="margin:8px 0 14px;">Paquetes (precio por día)</h3>

        <!-- 🧩 Contenedor dinámico de seguros -->
        <div id="packGrid" class="cards">
          @foreach($seguros as $seguro)
            <label class="card seguro-item" data-id="{{ $seguro->id_seguro }}" data-precio="{{ $seguro->precio_por_dia }}">
              <div class="body">
                <h4>{{ $seguro->nombre }}</h4>
                <p>{{ $seguro->cobertura }}</p>
                <div class="precio">
                  ${{ number_format($seguro->precio_por_dia, 2) }} MXN x Día
                </div>

                <div class="switch {{ $seguro->id_seguro == ($seguroSeleccionado->id_seguro ?? null) ? 'on' : '' }}"
                     data-id="{{ $seguro->id_seguro }}">
                </div>

                <div class="small" style="margin-top:8px;">Seleccionar Paquete</div>
              </div>
            </label>
          @endforeach
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
          <button class="btn primary" id="go4" type="button" {{ empty($seguroSeleccionado) ? 'disabled' : '' }}>Continuar →</button>
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
            <div class="hTitle"><div class="hIcon">🚗</div> Vehículo asignado</div>
            <button id="editVeh" class="btn" style="background:#fff;border:1px solid var(--stroke);">✏️ Editar</button>
          </div>
          <div class="body">
            <div class="kvline">
              <div class="k">Unidad</div>
              <div>
                <select id="vehAssign" disabled>
                  <option value="{{ $vehiculo->id_vehiculo ?? '' }}">
                    {{ $vehiculo->marca ?? '' }} {{ $vehiculo->modelo ?? '' }} ({{ $vehiculo->placa ?? '' }})
                  </option>
                </select>
                <div class="help" id="vehInfo" style="margin-top:6px">
                  Unidad seleccionada en la reservación.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 💰 CARGOS ADICIONALES (DINÁMICOS) -->
        <div class="card">
          <div class="head">
            <div class="hTitle"><div class="hIcon">💰</div> Cargos adicionales</div>
          </div>

          <div class="body">
            <div class="note">
              Activa los cargos opcionales que apliquen a este contrato.
            </div>

            <div id="cargosGrid" class="add-grid">
              @forelse($cargos_conceptos as $cargo)
                @php
                  $activo = DB::table('cargo_adicional')
                      ->where('id_contrato', $contrato->id_contrato ?? 0)
                      ->where('id_concepto', $cargo->id_concepto)
                      ->exists();
                @endphp

                <div class="card cargo-item"
                     data-id="{{ $cargo->id_concepto }}"
                     data-nombre="{{ $cargo->nombre }}"
                     data-monto="{{ $cargo->monto_base ?? 0 }}">
                  <div class="head">
                    <div class="hTitle">
                      <div class="hIcon">🧾</div> {{ $cargo->nombre }}
                    </div>
                    <div class="switch {{ $activo ? 'on' : '' }}"
                         data-id="{{ $cargo->id_concepto }}"
                         role="switch"
                         aria-label="{{ $cargo->nombre }}"></div>
                  </div>
                  <div class="body">
                    @if($cargo->descripcion)
                      <p style="margin-bottom:6px;">{{ $cargo->descripcion }}</p>
                    @endif
                    <div class="precio">
                      ${{ number_format($cargo->monto_base, 2) }} {{ $cargo->moneda }}
                    </div>
                  </div>
                </div>
              @empty
                <p>No hay conceptos de cargos adicionales configurados.</p>
              @endforelse
            </div>

            <!-- 💵 Total -->
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

        <!-- 🔹 Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back3" type="button">← Atrás</button>
          <button class="btn primary" id="go5" type="button">Continuar →</button>
        </div>

      </div>
    </section>
  </div>
</article>

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
              <label>Nombres</label>
              <input id="idNombre" name="nombre" type="text" placeholder="Nombre(s)" required autocomplete="off">
            </div>
            <div class="input-row">
              <label>Apellido Paterno</label>
              <input id="idApP" name="apellido_paterno" type="text" placeholder="Paterno" required autocomplete="off">
            </div>
            <div class="input-row">
              <label>Apellido Materno</label>
              <input id="idApM" name="apellido_materno" type="text" placeholder="Materno" required autocomplete="off">
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
              <label>Entidad/País de Emisión</label>
              <select id="licEmite" name="emite_licencia" required>
                <option value="">Selecciona…</option>
                <option>Ciudad de México</option><option>Aguascalientes</option><option>Baja California</option><option>Baja California Sur</option>
                <option>Campeche</option><option>Coahuila</option><option>Colima</option><option>Chiapas</option><option>Chihuahua</option>
                <option>Durango</option><option>Guanajuato</option><option>Guerrero</option><option>Hidalgo</option><option>Jalisco</option>
                <option>México</option><option>Michoacán</option><option>Morelos</option><option>Nayarit</option><option>Nuevo León</option>
                <option>Oaxaca</option><option>Puebla</option><option>Querétaro</option><option>Quintana Roo</option><option>San Luis Potosí</option>
                <option>Sinaloa</option><option>Sonora</option><option>Tabasco</option><option>Tamaulipas</option><option>Tlaxcala</option>
                <option>Veracruz</option><option>Yucatán</option><option>Zacatecas</option><option>Otro país</option>
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

          <!-- ⚠️ Advertencias visuales -->
          <div id="alertaLicencia" class="pill-warn" style="margin-top:8px; display:none;">
            ⚠️ Licencia vencida: por favor sube una licencia vigente para continuar.
          </div>
          <div id="confirmacionLicencia" class="pill-ok" style="margin-top:8px; display:none;">
            ✅ Licencia vigente verificada correctamente.
          </div>
        </div>
      </section>

      <!-- 🔹 Navegación -->
      <div class="acciones" style="margin-top:20px;">
        <button class="btn gray" id="back4" type="button">← Atrás</button>
        <button class="btn primary" id="btnContinuarDoc" type="submit">Guardar y Continuar →</button>
        <div class="small" style="margin-top:8px;">
          Se guarda automáticamente. Requisitos: fotos de frente y reverso de INE y Licencia.
        </div>
      </div>
    </form>

    <!-- 📦 BLOQUE DE CONDUCTORES ADICIONALES (oculto al inicio) -->
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
    <section class="section">
      <div class="head">Resumen</div>
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
          <div>Cargos e IVA</div>
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
      <button id="btnFinalizar" class="btn primary">FINALIZAR CONTRATO</button>
    </div>
  </div>
</article>

    </section>



    <!-- ======================
         SECCIÓN DERECHA (Resumen)
======================= -->
<aside class="sticky">
  <div class="card">
    <div class="head">Resumen del Contrato</div>
    <div class="cnt">
      <div id="vehiculo_info" style="text-align:center;">
        <img src="{{ asset('img/default-car.png') }}"
             alt="Vehículo"
             style="width:100%;max-width:240px;border-radius:12px;">

        <p><b>Nissan Versa</b></p>
        <p>Transmisión: Automático</p>
        <p>Puertas: 4</p>
        <p>Pasajeros: 5</p>
      </div>

      <div class="totalBox" style="margin-top:12px;">
        <div class="kv">
          <div>Total reservado</div>
          <div class="total">$5,000.00 MXN</div>
        </div>
      </div>
    </div>
  </div>
</aside>

  <!-- ======================
     MODAL · REGISTRAR PAGO
======================= -->
<div class="modal-back" id="mb">
  <div class="modal">
    <div class="head">
      Registrar Pago
      <button id="mx" class="btn gray" style="padding:6px 10px">✕</button>
    </div>
    <div class="body">
      <div class="pay-groups" id="payTabs">
        <button class="tab active" data-tab="tarjeta">Tarjeta</button>
        <button class="tab" data-tab="efectivo">Efectivo / Transferencia</button>
        <button class="tab" data-tab="cripto">Cripto</button>
      </div>

      <div id="methods">
        <!-- TARJETA -->
        <div data-pane="tarjeta">
          <div class="method-grid">
            <label class="mcard">
              <input type="radio" name="m" value="VISA">
              <img src="../assets/media/visa.png" alt="">
              <div><div class="ttl">VISA</div><div class="sub">Crédito/Débito</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="MASTERCARD">
              <img src="../assets/media/master.jpg" alt="">
              <div><div class="ttl">Mastercard</div><div class="sub">Crédito/Débito</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="AMEX">
              <img src="../assets/media/amex.png" alt="">
              <div><div class="ttl">AMEX</div><div class="sub">Crédito</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="DEBITO">
              <img src="../assets/media/debito.png" alt="">
              <div><div class="ttl">Débito</div><div class="sub">Terminal</div></div>
            </label>
          </div>
        </div>

        <!-- EFECTIVO -->
        <div data-pane="efectivo" style="display:none">
          <div class="method-grid">
            <label class="mcard">
              <input type="radio" name="m" value="EFECTIVO">
              <img src="../assets/media/efectivo.png" alt="">
              <div><div class="ttl">Efectivo</div><div class="sub">Caja</div></div>
            </label>
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
              <input type="radio" name="m" value="DEPÓSITO">
              <img src="../assets/media/deposito.png" alt="">
              <div><div class="ttl">Depósito</div><div class="sub">Sucursal</div></div>
            </label>
          </div>
        </div>

        <!-- CRIPTO -->
        <div data-pane="cripto" style="display:none">
          <div class="method-grid">
            <label class="mcard">
              <input type="radio" name="m" value="CRYPTO_BTC">
              <img src="../assets/media/b.png" alt="">
              <div><div class="ttl">Bitcoin</div><div class="sub">BTC</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="CRYPTO_ETH">
              <img src="../assets/media/e.png" alt="">
              <div><div class="ttl">Ethereum</div><div class="sub">ETH</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="CRYPTO_USDT">
              <img src="../assets/media/t.png" alt="">
              <div><div class="ttl">Tether</div><div class="sub">USDT</div></div>
            </label>
            <label class="mcard">
              <input type="radio" name="m" value="CRYPTO_BNB">
              <img src="../assets/media/bnb.png" alt="">
              <div><div class="ttl">BNB</div><div class="sub">BEP20</div></div>
            </label>
          </div>
        </div>
      </div>

      <fieldset>
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

          <!-- Tarjeta -->
          <div data-for="tarjeta">
            <label>Últimos 4 (opcional)</label>
            <input id="pUlt4" type="text" maxlength="4" placeholder="1234">
          </div>
          <div data-for="tarjeta">
            <label>Autorización (opcional)</label>
            <input id="pAuth" type="text" placeholder="APR-001234">
          </div>

          <!-- Cripto -->
          <div data-for="cripto" style="display:none">
            <label>Moneda / Red</label>
            <div class="inline">
              <select id="cCoin">
                <option value="BTC">BTC</option><option value="ETH">ETH</option>
                <option value="USDT">USDT</option><option value="BNB">BNB</option>
              </select>
              <select id="cNet"><option>On-chain</option></select>
            </div>
          </div>

          <div data-for="cripto" style="display:none">
            <label>Dirección (wallet receptora)</label>
            <div class="inline" style="flex:1">
              <input id="cAddr" type="text" placeholder="1ABc... o 0xABC..." value="TU_ADDRESS_AQUI" />
              <button type="button" id="copyAddr" class="copy">Copiar</button>
            </div>
          </div>

          <div data-for="cripto" style="display:none">
            <label>QR para el cliente</label>
            <div class="inline">
              <div class="qr"><canvas id="qrCanvas" width="110" height="110"></canvas></div>
              <div class="help">Placeholder sin librería (muestra texto).</div>
            </div>
          </div>

          <div data-for="cripto" style="display:none">
            <label>Hash / ID de transacción</label>
            <input id="cHash" type="text" placeholder="0x... / txid">
          </div>

          <div style="grid-column:1/-1">
            <label>Notas (opcional)</label>
            <textarea id="pNotes" rows="2" placeholder="Referencia, banco, comentario..."></textarea>
          </div>
        </div>
      </fieldset>
    </div>

    <div class="foot">
      <button id="pSave" class="btn primary">GUARDAR PAGO</button>
    </div>
  </div>
</div>
</main>
@endsection
@section('js-vistaContrato')

<script src="{{ asset('js/Contrato.js') }}"></script>
@endsection


