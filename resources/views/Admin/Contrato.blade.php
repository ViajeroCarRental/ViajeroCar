@extends('layouts.Ventas')
@section('Titulo', 'Contrato')

@section('css-vistaContrato')
<link rel="stylesheet" href="{{ asset('css/Contrato.css') }}">
@endsection

@section('contenidoContrato')
<main class="main">

  <h1 class="h1">Gestión de Contrato</h1>

  <div class="grid">

    <!-- ======================
         SECCIÓN IZQUIERDA
    ======================= -->
    <section class="steps">
      <!-- 🧾 PASO 1: DATOS DE RESERVACIÓN -->
      <article class="step" data-step="1">
        <header>
          <div class="badge">1</div>
          <h3>PASO 1 · Datos de la Reservación</h3>
        </header>

        <div class="body">
            <!-- Campo oculto de referencia -->
            <input type="hidden" name="id_reservacion" id="id_reservacion" value="{{ $reservacion->id_reservacion ?? '' }}">
            <input type="hidden" name="id_asesor" id="id_asesor" value="{{ session('id_usuario') }}">
            <input type="hidden" name="estado" value="abierto">

            <!-- Código de reservación -->
            <div>
              <label>Código de reservación</label>
              <input id="codigo" name="codigo" class="input" type="text"
                     value="{{ $reservacion->codigo ?? '' }}" readonly>
            </div>

            <!-- Nombre del cliente -->
            <div>
              <label>Nombre del cliente</label>
              <input id="nombre_cliente" name="nombre_cliente" class="input" type="text"
                     value="{{ $reservacion->nombre_cliente ?? '' }}" readonly>
            </div>

            <!-- Email del cliente -->
            <div>
              <label>Correo electrónico</label>
              <input id="email_cliente" name="email_cliente" class="input" type="email"
                     value="{{ $reservacion->email_cliente ?? '' }}" readonly>
            </div>

            <!-- Teléfono -->
            <div>
              <label>Teléfono</label>
              <input id="telefono_cliente" name="telefono_cliente" class="input" type="text"
                     value="{{ $reservacion->telefono_cliente ?? '' }}" readonly>
            </div>

            <!-- Fechas -->
            <div class="form-2">
              <div>
                <label>Fecha inicio</label>
                <input id="fecha_inicio" name="fecha_inicio" class="input" type="date"
                       value="{{ $reservacion->fecha_inicio ?? '' }}" readonly>
              </div>
              <div>
                <label>Hora de retiro</label>
                <input id="hora_retiro" name="hora_retiro" class="input" type="time"
                       value="{{ $reservacion->hora_retiro ?? '' }}" readonly>
              </div>
              <div>
                <label>Fecha fin</label>
                <input id="fecha_fin" name="fecha_fin" class="input" type="date"
                       value="{{ $reservacion->fecha_fin ?? '' }}" readonly>
              </div>
              <div>
                <label>Hora de entrega</label>
                <input id="hora_entrega" name="hora_entrega" class="input" type="time"
                       value="{{ $reservacion->hora_entrega ?? '' }}" readonly>
              </div>
            </div>

            <!-- Motivo apertura anticipada (opcional) -->
            <div>
              <label>Motivo de apertura anticipada (si aplica)</label>
              <textarea id="motivo_apertura_anticipada" name="motivo_apertura_anticipada"
                        class="input" placeholder="Escribe un motivo si el contrato se inicia antes de lo programado..."></textarea>
            </div>

            <!-- Botones -->
            <div class="acciones" style="margin-top:20px;">
              <button class="btn primary" id="btnGenerarContrato" type="submit">
                ✅ Generar número de contrato
              </button>
            </div>
        </div>
      </article>

      <!-- ======================
     PASO 2 · COMPLEMENTOS
======================= -->
<article class="step" data-step="2" style="display:none;">
  <header>
    <div class="badge">2</div>
    <h3>PASO 2 · Servicios adicionales</h3>
  </header>

  <div class="body">

    <!-- Sección de complementos -->
    <section class="section">
      <div class="head">Selecciona servicios adicionales</div>
      <div class="cnt">

        <!-- Contenedor donde se cargarán los servicios -->
        <div id="serviciosGrid" class="add-grid">
          <div class="loading" style="text-align:center;padding:12px;">
            Cargando servicios disponibles...
          </div>
        </div>

        <!-- Total parcial -->
        <div class="totalBox" style="margin-top:20px;">
          <div class="kv">
            <div>Total adicionales</div>
            <div class="total" id="total_servicios">$0.00 MXN</div>
          </div>
        </div>

        <!-- Navegación -->
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
<article class="step" data-step="3" style="display:none;">
  <header>
    <div class="badge">3</div>
    <h3>PASO 3 · Protecciones del contrato</h3>
  </header>

  <div class="body">
    <section class="section">
      <div class="head">Selecciona un paquete o protecciones individuales</div>
      <div class="cnt">

        <div class="note">
          Si eliges un <b>paquete</b>, se desactivan las individuales.
          Si activas alguna <b>individual</b>, se desmarca el paquete.
        </div>

        <!-- 🔹 Paquetes (desde DB) -->
        <h3 style="margin:4px 0 10px;">Paquetes (precio por día)</h3>
        <div id="packGrid" class="cards">
          @foreach($seguros as $seguro)
            <label class="card seguro-item">
              <input type="radio" name="id_paquete" value="{{ $seguro->id_paquete }}"
                     data-precio="{{ $seguro->precio_por_dia }}" class="seguro-check">
              <div class="body">
                <h4>{{ $seguro->nombre }}</h4>
                <p>{{ $seguro->descripcion ?? 'Sin descripción disponible.' }}</p>
                <div class="precio">
                  ${{ number_format($seguro->precio_por_dia, 2) }} MXN / día
                </div>
              </div>
            </label>
          @endforeach
        </div>

        <!-- 🔸 Total visual -->
        <div class="totalBox" style="margin-top:16px;">
          <div class="kv">
            <div>Total protecciones</div>
            <div class="total" id="total_seguros">$0.00 MXN</div>
          </div>
        </div>

        <!-- 🔹 Navegación -->
        <div class="acciones" style="margin-top:20px;">
          <button class="btn gray" id="back2" type="button">← Atrás</button>
          <button class="btn primary" id="go4" type="button">Continuar →</button>
        </div>
      </div>
    </section>
  </div>
</article>

    </section>



    <!-- ======================
         SECCIÓN DERECHA (Resumen)
    ======================= -->
    <aside class="sticky">
      <div class="card">
        <div class="head">Datos del vehículo</div>
        <div class="cnt">
          <div id="vehiculo_info" style="text-align:center;">
            <img src="{{ $vehiculo->imagen ?? asset('assets/media/nissan.gif') }}"
                 alt="Vehículo" style="width:100%;max-width:240px;border-radius:12px;">
            <p><b>{{ $vehiculo->marca ?? '' }} {{ $vehiculo->modelo ?? '' }}</b></p>
            <p>Transmisión: {{ $vehiculo->transmision ?? 'AUTOMÁTICO' }}</p>
            <p>Puertas: {{ $vehiculo->puertas ?? '5' }}</p>
            <p>Pasajeros: {{ $vehiculo->pasajeros ?? '5' }}</p>
          </div>

          <div class="totalBox">
            <div class="kv"><div>Total reservado</div><div class="total">${{ number_format($reservacion->total ?? 0,2) }} MXN</div></div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

@section('js-vistaContrato')

<script src="{{ asset('js/Contrato.js') }}"></script>
@endsection

@endsection
