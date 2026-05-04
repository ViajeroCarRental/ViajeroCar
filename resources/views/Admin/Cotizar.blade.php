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

            <form id="formCotizacion" action="{{ route('rutaGuardarCotizacion') }}" method="POST" novalidate>
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
                                    @foreach ($sucursales as $ciudad => $grupo)
                                        @if ($ciudad === 'Querétaro')
                                            <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
                                                @foreach ($grupo as $s)
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
                                    <input type="text" name="numero_vuelo" id="numero_vuelo" class="input"
                                        placeholder="Ej. AA1234">
                                </div>
                            </div>

                            <!-- ENTREGA -->
                            <div>
                                <label>Sucursal de entrega</label>
                                <select id="sucursal_entrega" name="sucursal_entrega" class="input" required>
                                    <option value="">Selecciona punto de devolución</option>
                                    @foreach ($sucursales as $ciudad => $grupo)
                                        <optgroup label="{{ $ciudad }} — {{ $ciudad }}">
                                            @foreach ($grupo as $s)
                                                <option value="{{ $s->id_sucursal }}" data-ciudad-id="{{ $s->id_ciudad }}"
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
                                <input id="fecha_inicio_ui" class="input input-lg" type="text" placeholder="Fecha"
                                    autocomplete="off">
                                <input id="fecha_inicio" type="hidden">
                            </div>

                            <!-- HORA DE SALIDA -->
                            <div class="dt-field icon-field time-field">
                                <label>Hora de salida</label>
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input id="hora_retiro_ui" class="input input-lg" type="text" placeholder="hh:mm"
                                    autocomplete="off">
                                <input id="hora_retiro" type="hidden">
                            </div>

                            <!-- FECHA DE LLEGADA -->
                            <div class="dt-field icon-field">
                                <label>Fecha de llegada</label>
                                <span class="field-icon"><i class="fa-regular fa-calendar-days"></i></span>
                                <input id="fecha_fin_ui" class="input input-lg" type="text" placeholder="Fecha"
                                    autocomplete="off">
                                <input id="fecha_fin" type="hidden">
                            </div>

                            <!-- HORA DE LLEGADA -->
                            <div class="dt-field icon-field time-field">
                                <label>Hora de llegada</label>
                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                <input id="hora_entrega_ui" class="input input-lg" type="text" placeholder="hh:mm"
                                    autocomplete="off">
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
                        <div class="stack-sub">Selecciona una categoría. Mostramos tarifa base por día + cálculo previo.
                        </div>
                    </div>

                    <div class="stack-body">
                        <div class="picker-row">
                            <button class="btn primary" type="button" id="btnCategorias">📦 Seleccionar
                                categoría</button>
                            <div class="picker-selected">
                                <div class="picker-label">Seleccionado</div>
                                <div class="picker-value" id="catSelTxt">— Ninguna categoría —</div>
                                <div class="picker-sub" id="catSelSub">Tarifa base por día y cálculo previo aparecerán
                                    aquí.</div>
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
               PASO 4: SERVICIOS (DROP OFF, DELIVERY, GASOLINA)
          ========================================= -->
                <section class="stack-card">
                    <div class="stack-head">
                        <div class="stack-title">🧰 Servicios</div>
                        <div class="stack-sub">Servicios adicionales.</div>
                    </div>

                    <div class="stack-body">
                        <div class="svc-grid">

                            {{-- 🚩 DROP OFF --}}
                            <div class="svc-card svc-card--accent dropoff-wrapper">
                                <div class="svc-top">
                                    <div class="svc-ico">🚩</div>
                                    <div class="svc-meta">
                                        <div class="svc-name">Drop Off</div>
                                        <div class="svc-desc">Entrega en sucursal distinta.</div>
                                    </div>
                                </div>

                                <div class="svc-bottom">
                                    <div class="svc-hint">Activar</div>
                                    <label class="switch switch-soft">
                                        <input type="checkbox" id="dropoffToggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="svc-fields" id="dropoffFields" style="display: none;">
                                    <div class="svc-field">
                                        <label class="svc-label">Ubicación de devolución</label>
                                        <select id="dropUbicacion" class="input">
                                            <option value="">Seleccione...</option>
                                            @if (isset($ubicaciones))
                                                @foreach ($ubicaciones as $u)
                                                    <option value="{{ $u->id_ubicacion }}"
                                                        data-km="{{ $u->km ?? 0 }}">
                                                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }}
                                                        km)
                                                    </option>
                                                @endforeach
                                            @endif
                                            <option value="0">Dirección personalizada</option>
                                        </select>
                                    </div>

                                    <div class="svc-field" id="dropGroupDireccion" style="display: none;">
                                        <label class="svc-label">Dirección</label>
                                        <input type="text" id="dropDireccion" class="input"
                                            placeholder="Calle, No, Colonia...">
                                    </div>

                                    <div class="svc-field" id="dropGroupKm" style="display: none;">
                                        <label class="svc-label">Kilómetros</label>
                                        <input type="number" id="dropKm" class="input" placeholder="0">
                                    </div>

                                    <div class="svc-total">
                                        <span>Total Drop Off</span>
                                        <b id="dropTotal">$0.00 MXN</b>
                                    </div>
                                </div>
                                <input type="hidden" id="dropoffTotalHidden" value="0">
                            </div>

                            {{-- 🚚 DELIVERY --}}
                            <div class="svc-card svc-card--accent delivery-wrapper">
                                <div class="svc-top">
                                    <div class="svc-ico">🚚</div>
                                    <div class="svc-meta">
                                        <div class="svc-name">Delivery</div>
                                        <div class="svc-desc">Entrega a domicilio.</div>
                                    </div>
                                </div>

                                <div class="svc-bottom">
                                    <div class="svc-hint">Activar</div>
                                    <label class="switch switch-soft">
                                        <input type="checkbox" id="deliveryToggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="svc-fields" id="deliveryFields" style="display: none;">
                                    <div class="svc-field">
                                        <label class="svc-label">Seleccionar ubicación</label>
                                        <select id="deliveryUbicacion" class="input">
                                            <option value="">Seleccione...</option>
                                            @if (isset($ubicaciones))
                                                @foreach ($ubicaciones as $u)
                                                    <option value="{{ $u->id_ubicacion }}"
                                                        data-km="{{ $u->km ?? 0 }}">
                                                        {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }}
                                                        km)
                                                    </option>
                                                @endforeach
                                            @endif
                                            <option value="0">Dirección personalizada</option>
                                        </select>
                                    </div>

                                    <div class="svc-field" id="groupDireccion" style="display: none;">
                                        <label class="svc-label">Dirección</label>
                                        <input type="text" id="deliveryDireccion" class="input"
                                            placeholder="Calle, No, Colonia...">
                                    </div>

                                    <div class="svc-field" id="groupKm" style="display: none;">
                                        <label class="svc-label">Kilómetros</label>
                                        <input type="number" id="deliveryKm" class="input" placeholder="0">
                                    </div>

                                    <div class="svc-total">
                                        <span>Total Delivery</span>
                                        <b id="deliveryTotal">$0.00 MXN</b>
                                    </div>
                                </div>
                                <input type="hidden" id="deliveryTotalHidden" value="0">
                            </div>

                            {{-- ⛽ GASOLINA PREPAGO --}}
                            <div class="svc-card svc-card--accent">
                                <div class="svc-top">
                                    <div class="svc-ico">⛽</div>
                                    <div class="svc-meta">
                                        <div class="svc-name">Gasolina prepago</div>
                                        <div class="svc-desc">Tanque completo preferencial.</div>
                                    </div>
                                </div>

                                <div class="svc-bottom">
                                    <div class="svc-hint">Activar</div>
                                    <label class="switch switch-soft">
                                        <input type="checkbox" id="gasolinaToggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="svc-fields" id="gasolinaFields" style="display: none;">
                                    <div class="svc-total">
                                        <span>Total Gasolina (<span id="litrosLabel">0</span>L)</span>
                                        <b id="gasolinaTotal">$0.00 MXN</b>
                                    </div>
                                </div>
                                <input type="hidden" name="gasolina_prepago_valor" id="gasolinaTotalHidden"
                                    value="0">
                            </div>

                        </div>

                        <!-- Hidden inputs para servicios -->
                        <input type="hidden" id="svc_dropoff" name="svc_dropoff" value="0">
                        <input type="hidden" id="svc_delivery" name="svc_delivery" value="0">
                        <input type="hidden" id="svc_gasolina" name="svc_gasolina" value="0">
                        <input type="hidden" id="deliveryPrecioKm" value="0">
                        <input type="hidden" id="gasolinaPrecioLitro" value="20">
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
                            <button class="btn primary" type="button" id="btnProtecciones">🛡️ Seleccionar
                                protección</button>
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
                                <input id="telefono_cliente" class="input" type="text" placeholder="+52..."
                                    required>
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
                            <button class="btn success" id="btnGuardarYEnviar" type="button">💾 Guardar y
                                enviar</button>
                            <button class="btn primary" id="btnConfirmarCotizacion" type="button">✅ Confirmar y
                                reservar</button>
                        </div>
                    </div>
                </section>

                <!-- BOTÓN VER COTIZACIONES -->
                <div class="ver-cotizaciones-wrap">
                    <a href="{{ route('rutaVerCotizaciones') }}" class="btn-ver-cotizaciones"
                        style="text-decoration: none; display: inline-block;">📄 Ver cotizaciones</a>
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
         MODAL: PROTECCIONES (CARRUSEL + DECLINE + INDIVIDUALES)
    ========================================= -->
    <div class="pop modal" id="proteccionPop">
        <div class="box modal-box" style="max-width: 1000px;">
            <header class="modal-head">
                <div class="modal-title">🔒 Protecciones</div>
                <button class="btn gray" id="proteClose" type="button">✖</button>
            </header>

            <div class="modal-body" style="padding: 0;">
                <div class="prote-content-wrapper">
                    <!-- ✅ BOTONES EN LA ESQUINA SUPERIOR IZQUIERDA -->
                    <div class="prote-buttons-left">
                        <button type="button" id="btnIndividualesModal" class="btn-individuales-inline">🧩 Protecciones
                            Individuales</button>
                        <button type="button" id="btnDeclineModal" class="btn-decline-inline">⚠️ Decline
                            Protections</button>
                    </div>
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
    {{-- ✅ MODAL: PROTECCIONES INDIVIDUALES (ESTILO PAQUETES - TÍTULO ARRIBA, PRECIO Y BOTÓN ABAJO) --}}
    <div class="pop modal" id="proteccionIndividualPop">
        <div class="box modal-box" style="max-width: 950px;">
            <header class="modal-head">
                <div class="modal-title">🧩 Protecciones individuales</div>
                <button class="btn gray" id="proteIndividualClose" type="button">✖</button>
            </header>
            <style>
                /* =========================================
           1. CONTENEDOR PRINCIPAL Y SCROLL
        ========================================= */
                #proteccionIndividualPop .modal-body {
                    max-height: 70vh;
                    overflow-y: auto;
                    padding: 20px;
                }

                #proteccionIndividualPop .scroll-h {
                    display: flex;
                    gap: 20px;
                    overflow-x: auto;
                    padding: 10px 6px 25px 6px;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                }

                #proteccionIndividualPop .scroll-h::-webkit-scrollbar {
                    height: 8px;
                }

                #proteccionIndividualPop .scroll-h::-webkit-scrollbar-thumb {
                    background: #cbd5e1;
                    border-radius: 999px;
                }

                /* =========================================
           2. TÍTULOS DE CATEGORÍA
        ========================================= */
                #proteccionIndividualPop .cat-title {
                    margin: 28px 0 16px 0;
                    font-weight: 900;
                    color: #111827;
                    text-transform: uppercase;
                    letter-spacing: 0.02em;
                    font-size: 15px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                #proteccionIndividualPop .cat-title::after {
                    content: "";
                    flex: 1;
                    height: 3px;
                    background: linear-gradient(90deg, #f44336, #ff6b6b, transparent);
                    border-radius: 3px;
                }

                #proteccionIndividualPop .cat-title:first-of-type {
                    margin-top: 0;
                }

                /* =========================================
           3. TARJETAS (MÁS COMPACTAS HORIZONTALMENTE)
        ========================================= */
                #proteccionIndividualPop .ins-card-pack {
                    flex: 0 0 285px;
                    /* <--- Ancho reducido para que sea más compacto */
                    scroll-snap-align: start;
                    background: #fff;
                    border-radius: 20px;
                    border: 1px solid rgba(15, 23, 42, 0.08);
                    box-shadow: 0 14px 38px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
                    position: relative;
                    cursor: pointer;
                    min-height: 520px;
                    display: flex;
                    flex-direction: column;
                }

                #proteccionIndividualPop .ins-card-pack::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 6px;
                    background: linear-gradient(90deg, #f44336, #ff6b6b);
                    z-index: 2;
                }

                #proteccionIndividualPop .ins-card-pack:hover {
                    transform: translateY(-6px);
                    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.14);
                    border-color: rgba(244, 67, 54, 0.25);
                }

                /* =========================================
           4. CUERPO DE LA TARJETA (LLENADO DE ESPACIO)
        ========================================= */
                #proteccionIndividualPop .ins-pack-body {
                    padding: 25px 18px;
                    /* Padding equilibrado */
                    display: flex;
                    flex-direction: column;
                    flex: 1;
                    height: 100%;
                }

                /* TÍTULO - Arriba */
                #proteccionIndividualPop .ins-pack-title {
                    margin: 0 0 10px 0;
                    font-size: 20px;
                    font-weight: 900;
                    color: #111827;
                    letter-spacing: -0.2px;
                    line-height: 1.2;
                    text-align: center;
                }

                /* BENEFICIOS - ESTO LLENA EL "ESPACIO VERDE" DE TU DIBUJO */
                #proteccionIndividualPop .ins-pack-benefits {
                    margin: 0;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-evenly;
                    /* Separa los textos para ocupar todo el alto */
                    padding: 15px 0;
                }

                /* CADA RENGLÓN DE TEXTO (MÁS GRANDE) */
                #proteccionIndividualPop .ins-pack-bullet {
                    font-size: 18px;
                    color: #374151;
                    font-weight: 600;
                    line-height: 1.3;
                    text-align: center;
                    width: 100%;
                    list-style: none;
                    padding: 0;
                }

                /* =========================================
           5. PRECIO Y BOTÓN (TU ESTILO ORIGINAL)
        ========================================= */
                #proteccionIndividualPop .ins-pack-bottom {
                    margin-top: auto;
                    padding-top: 16px;
                    border-top: 1px solid rgba(15, 23, 42, 0.08);
                    display: flex;
                    flex-direction: column;
                    gap: 14px;
                }

                #proteccionIndividualPop .ins-pack-price {
                    font-size: 24px;
                    font-weight: 900;
                    color: #f44336;
                    line-height: 1.2;
                    text-align: center;
                }

                #proteccionIndividualPop .ins-pack-price span {
                    font-size: 11px;
                    font-weight: 800;
                    color: #6b7280;
                    text-transform: uppercase;
                }

                /* BOTÓN SELECCIONAR (MANTENIDO TU GRADIENTE Y ESTILO) */
                #proteccionIndividualPop .btn-ins-pack-select {
                    width: 100%;
                    background: linear-gradient(180deg, #f44336, #d32f2f);
                    border: none;
                    border-radius: 14px;
                    padding: 14px 16px;
                    font-weight: 900;
                    font-size: 14px;
                    color: white;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                #proteccionIndividualPop .btn-ins-pack-select:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(244, 67, 54, 0.3);
                }

                /* =========================================
           6. ESTADOS Y RESPONSIVO
        ========================================= */
                #proteccionIndividualPop .ins-card-pack.is-selected {
                    border-color: #f44336;
                    box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.12), 0 20px 40px rgba(0, 0, 0, 0.12);
                }

                #proteccionIndividualPop .ins-card-pack.is-selected::after {
                    content: "✓ SELECCIONADO";
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: #111827;
                    color: white;
                    padding: 5px 12px;
                    border-radius: 30px;
                    font-weight: 800;
                    font-size: 10px;
                    z-index: 3;
                }

                @media (max-width: 560px) {
                    #proteccionIndividualPop .ins-card-pack {
                        flex-basis: 260px;
                        min-height: 480px;
                    }

                    #proteccionIndividualPop .ins-pack-bullet {
                        font-size: 16px;
                    }
                }
            </style>
            <div class="modal-body">
                <!-- COLISIÓN Y ROBO -->
                <div class="cat-title">🚗 Colisión y robo</div>
                <div class="scroll-h" id="insColisionTrack">
                    @forelse(($grupo_colision ?? []) as $ind)
                        <div class="ins-card-pack individual-item-cotizacion" data-id="{{ $ind->id_individual }}"
                            data-precio="{{ $ind->precio_por_dia }}" data-nombre="{{ $ind->nombre }}"
                            data-desc="{{ $ind->descripcion }}">
                            <div class="ins-pack-body">
                                <h4 class="ins-pack-title">{{ $ind->nombre }}</h4>
                                <div class="ins-pack-benefits">
                                    @php
                                        $beneficios = explode("\n", $ind->descripcion);
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @if (trim($beneficio))
                                            {{-- ✅ SIN EL PUNTO • --}}
                                            <div class="ins-pack-bullet">{{ trim($beneficio) }}</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="ins-pack-bottom">
                                    <div class="ins-pack-price">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN /
                                            día</span></div>
                                    <button class="btn-ins-pack-select">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                <!-- GASTOS MÉDICOS -->
                <div class="cat-title">🏥 Gastos médicos</div>
                <div class="scroll-h" id="insMedicosTrack">
                    @forelse(($grupo_medicos ?? []) as $ind)
                        <div class="ins-card-pack individual-item-cotizacion" data-id="{{ $ind->id_individual }}"
                            data-precio="{{ $ind->precio_por_dia }}" data-nombre="{{ $ind->nombre }}"
                            data-desc="{{ $ind->descripcion }}">
                            <div class="ins-pack-body">
                                <h4 class="ins-pack-title">{{ $ind->nombre }}</h4>
                                <div class="ins-pack-benefits">
                                    @php
                                        $beneficios = explode("\n", $ind->descripcion);
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @if (trim($beneficio))
                                            {{-- ✅ SIN EL PUNTO • --}}
                                            <div class="ins-pack-bullet">{{ trim($beneficio) }}</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="ins-pack-bottom">
                                    <div class="ins-pack-price">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN /
                                            día</span></div>
                                    <button class="btn-ins-pack-select">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                <!-- ASISTENCIA PARA EL CAMINO -->
                <div class="cat-title">🛣️ Asistencia para el camino</div>
                <div class="scroll-h" id="insCaminoTrack">
                    @forelse(($grupo_asistencia ?? []) as $ind)
                        <div class="ins-card-pack individual-item-cotizacion" data-id="{{ $ind->id_individual }}"
                            data-precio="{{ $ind->precio_por_dia }}" data-nombre="{{ $ind->nombre }}"
                            data-desc="{{ $ind->descripcion }}">
                            <div class="ins-pack-body">
                                <h4 class="ins-pack-title">{{ $ind->nombre }}</h4>
                                <div class="ins-pack-benefits">
                                    @php
                                        $beneficios = explode("\n", $ind->descripcion);
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @if (trim($beneficio))
                                            {{-- ✅ SIN EL PUNTO • --}}
                                            <div class="ins-pack-bullet">{{ trim($beneficio) }}</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="ins-pack-bottom">
                                    <div class="ins-pack-price">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN /
                                            día</span></div>
                                    <button class="btn-ins-pack-select">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                <!-- DAÑOS A TERCEROS -->
                <div class="cat-title">⚖️ Daños a terceros</div>
                <div class="scroll-h" id="insTercerosTrack">
                    @forelse(($grupo_terceros ?? []) as $ind)
                        <div class="ins-card-pack individual-item-cotizacion" data-id="{{ $ind->id_individual }}"
                            data-precio="{{ $ind->precio_por_dia }}" data-nombre="{{ $ind->nombre }}"
                            data-desc="{{ $ind->descripcion }}">
                            <div class="ins-pack-body">
                                <h4 class="ins-pack-title">{{ $ind->nombre }}</h4>
                                <div class="ins-pack-benefits">
                                    @php
                                        $beneficios = explode("\n", $ind->descripcion);
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @if (trim($beneficio))
                                            {{-- ✅ SIN EL PUNTO • --}}
                                            <div class="ins-pack-bullet">{{ trim($beneficio) }}</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="ins-pack-bottom">
                                    <div class="ins-pack-price">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN /
                                            día</span></div>
                                    <button class="btn-ins-pack-select">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                <!-- PROTECCIONES AUTOMÁTICAS -->
                <div class="cat-title">🔧 Protecciones automáticas</div>
                <div class="scroll-h" id="insAutoTrack">
                    @forelse(($grupo_protecciones ?? []) as $ind)
                        <div class="ins-card-pack individual-item-cotizacion" data-id="{{ $ind->id_individual }}"
                            data-precio="{{ $ind->precio_por_dia }}" data-nombre="{{ $ind->nombre }}"
                            data-desc="{{ $ind->descripcion }}">
                            <div class="ins-pack-body">
                                <h4 class="ins-pack-title">{{ $ind->nombre }}</h4>
                                <div class="ins-pack-benefits">
                                    @php
                                        $beneficios = explode("\n", $ind->descripcion);
                                    @endphp
                                    @foreach ($beneficios as $beneficio)
                                        @if (trim($beneficio))
                                            {{-- ✅ SIN EL PUNTO • --}}
                                            <div class="ins-pack-bullet">{{ trim($beneficio) }}</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="ins-pack-bottom">
                                    <div class="ins-pack-price">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN /
                                            día</span></div>
                                    <button class="btn-ins-pack-select">Seleccionar</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>
            </div>

            <footer class="modal-foot foot-split">
                <button class="btn gray" id="proteIndividualCancel" type="button">Cerrar</button>
                <button class="btn primary" id="proteIndividualApply" type="button">Aplicar selección</button>
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
                    <li>Asistencia Premium: El cliente es responsable por costos de grúa, corralón, envío de llaves o
                        gasolina, apertura de auto, cambio de neumático ponchado y paso de corriente.</li>
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
                            <button type="button" id="btnEditarTarifa"
                                style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:16px;margin-left:6px;">
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
                        <div><i class='bx bx-wrench'></i> Servicios</div>
                        <div id="resServicios">—</div>
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

                    <div class="res-row">
                        <div>Subtotal</div>
                        <div id="resSub">$0.00 MXN</div>
                    </div>
                    <div class="res-row">
                        <div>IVA (16%)</div>
                        <div id="resIva">$0.00 MXN</div>
                    </div>
                    <div class="res-row total">
                        <div>Total</div>
                        <div id="resTotal">$0.00 MXN</div>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- MAPA DE ICONOS -->
    <script>
        window.iconosPorId = {
            @foreach ($sucursales as $ciudad => $grupo)
                @foreach ($grupo as $s)
                    @php
                        $nombre = strtolower($s->sucursal);
                        $icono = 'fa-building';

                        if (str_contains($nombre, 'aeropuerto')) {
                            $icono = 'fa-plane-departure';
                        } elseif ((str_contains($nombre, 'central') || str_contains($nombre, 'autobuses')) && !str_contains($nombre, 'plaza central park')) {
                            $icono = 'fa-bus';
                        } elseif (str_contains($nombre, 'oficina') || str_contains($nombre, 'plaza central park') || str_contains($nombre, 'plaza')) {
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
