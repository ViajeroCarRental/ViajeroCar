@extends('layouts.Ventas')
@section('Titulo', 'cotizacionesAdmin')

@section('css-vistaCotizar')
    <link rel="stylesheet" href="{{ asset('css/Cotizar.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('contenidoCotizar')

@php
  $edit = isset($cotizacion) && !empty($cotizacion->id_cotizacion);
@endphp

<div class="wrap">
  <main class="main">

    <div class="top">
      <h1 class="h1"><i class="fas fa-plus-circle"></i> Nueva cotización</h1>

      <div class="top-actions">
        <button class="btn btn-resumen" id="btnResumen" type="button">
            <span class="pulse-dot"></span> <i class="fas fa-money-bill-wave"></i>
            <span id="btnTotalText">Total: $0.00 MXN</span>
        </button>
        <button class="btn ghost" onclick="location.href='{{ route('rutaCotizaciones') }}'"><i class="fas fa-sign-out-alt"></i> Salir</button>
      </div>
    </div>

    <form
  id="formCotizacion"
  action="{{ $edit
      ? route('cotizaciones.update', $cotizacion->id_cotizacion ?? '')
      : route('rutaGuardarCotizacion') }}"
  method="POST"
   novalidate
>
  @csrf

  @if($edit)
    @method('PUT')
  @endif

      {{-- Hidden "state" --}}
      <input type="hidden" id="categoria_id" name="categoria_id"
      value="{{ $cotizacion->id_categoria ?? '' }}">
      <input type="hidden" id="proteccion_id" name="proteccion_id" value="">
      <div id="addonsHidden"></div>

      {{-- ✅ FIX: ciudades (FK -> id_ciudad) --}}
      <input type="hidden" id="ciudad_retiro"  name="ciudad_retiro"  value="">
      <input type="hidden" id="ciudad_entrega" name="ciudad_entrega" value="">

      {{-- ✅ Servicios (switch) --}}
      <input type="hidden" id="svc_dropoff"  name="svc_dropoff"  value="0">
      <input type="hidden" id="svc_delivery" name="svc_delivery" value="0">
      <input type="hidden" id="svc_gasolina" name="svc_gasolina" value="0">

      {{-- ✅ Precio por litro (Gasolina prepago) -> lo usa el JS --}}
      <input type="hidden" id="gasolinaPrecioLitro" value="24">

      {{-- Hidden wrap para individuales --}}
      <div id="insHidden"></div>

      {{-- ✅ Teléfono final (backend) --}}
      <input type="hidden" id="telefono_cliente" name="telefono_cliente" value="{{ $cotizacion->telefono_cliente ?? '' }}">

      <input type="hidden" id="telefono_lada" name="telefono_lada" value="+52">

{{-- ======================
     SECCIÓN UNIFICADA - DISEÑO HORIZONTAL
======================= --}}
<section class="stack-card">
    <div class="stack-body">
        <div class="search-grid-admin">

            {{-- COLUMNA 1: UBICACIÓN --}}
            <div class="sg-col-location-admin">
                <div class="location-head-admin">
                    <span class="field-title-admin">PICK-UP</span>
                    <label class="inline-check-admin" for="differentDropoffAdmin">
                        <input type="checkbox" id="differentDropoffAdmin" name="different_dropoff" value="1">
                        <span>DEVOLVER EN OTRO DESTINO</span>
                    </label>
                </div>

                <div class="location-inputs-wrapper-admin">
                    {{-- SELECT PICKUP --}}
                <div class="field-admin">
                    <select id="sucursal_retiro" name="sucursal_retiro" class="input-buscador-admin" required style="padding-left: 40px !important;">
                        <option value="" disabled selected>¿Dónde comienza tu viaje?</option>
                        @foreach($sucursales as $ciudad => $grupo)
                            @if($ciudad === 'Querétaro')
                                <optgroup label="{{ $ciudad }}">
                                    @foreach($grupo as $s)
                                        <option value="{{ $s->id_sucursal }}" data-icon="{{ $s->icono ?? 'fa-location-dot' }}">{{ $s->sucursal }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- SELECT DROPOFF (oculto por defecto) --}}
                <div class="field-admin" id="dropoffWrapperAdmin" style="display: none;">
                    <select id="sucursal_entrega" name="sucursal_entrega" class="input-buscador-admin" disabled style="padding-left: 40px !important;">
                        <option value="" disabled selected>¿Dónde termina tu viaje?</option>
                        @foreach($sucursales as $ciudad => $grupo)
                            <optgroup label="{{ $ciudad }}">
                                @foreach($grupo as $s)
                                    <option value="{{ $s->id_sucursal }}">{{ $s->sucursal }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                </div>
            </div>

            {{-- COLUMNA 2: FECHAS Y HORAS --}}
            <div class="sg-col-datetime-admin">
                {{-- PICKUP --}}
                <div class="field-admin">
                    <span class="field-title-admin solo-responsivo-izq">PICK-UP</span>
                    <div class="datetime-row-admin">
                        <div class="dt-field-admin icon-field-admin">
                            <span class="field-icon-admin"><i class="fa-regular fa-calendar-days"></i></span>
                            <input id="fecha_inicio_ui" class="input-buscador-admin" type="text" placeholder="Fecha" autocomplete="off">
                            <input id="fecha_inicio" name="fecha_inicio" type="hidden">
                        </div>
                        <div class="dt-field-admin icon-field-admin time-field-admin">
                            <span class="field-icon-admin"><i class="fa-regular fa-clock"></i></span>
                            <input id="hora_retiro_ui" class="input-buscador-admin" type="text" placeholder="Hora" autocomplete="off">
                            <input id="hora_retiro" name="hora_retiro" type="hidden">
                        </div>
                    </div>
                </div>

                {{-- DROPOFF --}}
                <div class="field-admin">
                    <span class="field-title-admin solo-responsivo-izq">DEVOLUCIÓN</span>
                    <div class="datetime-row-admin">
                        <div class="dt-field-admin icon-field-admin">
                            <span class="field-icon-admin"><i class="fa-regular fa-calendar-days"></i></span>
                            <input id="fecha_fin_ui" class="input-buscador-admin" type="text" placeholder="Fecha" autocomplete="off">
                            <input id="fecha_fin" name="fecha_fin" type="hidden">
                        </div>
                        <div class="dt-field-admin icon-field-admin time-field-admin">
                            <span class="field-icon-admin"><i class="fa-regular fa-clock"></i></span>
                            <input id="hora_entrega_ui" class="input-buscador-admin" type="text" placeholder="Hora" autocomplete="off">
                            <input id="hora_entrega" name="hora_entrega" type="hidden">
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA 3: CONTADOR + BOTÓN --}}
            <div class="sg-col-submit-admin">
                <div class="actions-admin">
                    <div class="days-pill-admin">
                        <i class="fa-regular fa-clock"></i>
                        <span id="diasTxt">0</span> día(s)
                    </div>
                    <button type="button" id="btnBuscarCotizacion" class="btn-buscar-admin">
                        <i class="fa-solid fa-magnifying-glass"></i> BUSCAR
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ======================
       3) CATEGORÍA
======================= --}}
<section class="stack-card acordeon-item"
         data-seccion="categoria"
         data-siguiente="adicionales">

    <div class="stack-head">

        <div class="stack-title">
            <i class="fas fa-car"></i>
            Categoría
        </div>

        <div class="stack-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>

    </div>

    <div class="stack-body">

        {{-- CONTENEDOR DINÁMICO - Todo se genera desde JS --}}
        <div id="categoriaContainer"></div>

    </div>

</section>

      {{-- ======================
     4) SERVICIOS / ADICIONALES (CARRUSEL)
======================= --}}
@php
  $deliverySafe = $delivery ?? null;
  $ubicacionesSafe = $ubicaciones ?? [];
  $costoKmCategoriaSafe = $costoKmCategoria ?? 0;
  $idCotizacionSafe = $cotizacion->id_cotizacion ?? null;
@endphp

<section class="stack-card acordeon-item" data-seccion="adicionales" data-siguiente="protecciones">
  <div class="stack-head">
    <div class="stack-title"><i class="fas fa-tools"></i> Adicionales</div>
    <div class="stack-indicator"><i class="fas fa-chevron-down"></i></div>
  </div>

  <div class="stack-body">

    {{-- CARRUSEL DE CARDS --}}
    <div class="adicionales-carousel">
      <button class="carousel-arrow prev" type="button" aria-label="Anterior">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <div class="carousel-container">
        <div class="carousel-track" id="adicionalesTrack">

            {{-- CARD 1: CONDUCTOR ADICIONAL --}}
          <div class="svc-card svc-card--addon carousel-item" data-id="conductor_extra" data-name="Conductor adicional" data-price="200" data-charge="por_dia">
            <div class="svc-top">
              <div class="svc-ico"><i class="fas fa-user-plus"></i></div>
              <div class="svc-meta">
                <div class="svc-name">Conductor adicional</div>
              </div>
            </div>

            <div class="svc-bottom">
              <label class="switch switch-soft">
                <input type="checkbox" class="addon-toggle" data-addon="conductor_extra">
                <span class="slider"></span>
              </label>
            </div>

            <div class="svc-addon-expanded" id="conductorExtraExpanded" style="display: none;">
              <div class="svc-price-row">
                <div class="price-label">Costo</div>
                <div class="price-value">$200 MXN <span>/ día</span></div>
              </div>

              <div class="svc-quantity-row">
                <div class="quantity-control">
                  <button class="qty-btn minus" type="button">−</button>
                  <span class="qty-value" data-qty="1">1</span>
                  <button class="qty-btn plus" type="button">+</button>
                  <span class="max-hint">Máx 3</span>
                </div>
              </div>

              <div class="svc-total-row">
                <span>Total Conductor adicional</span>
                <b class="addon-total">$200.00 MXN</b>
              </div>
            </div>
            <input type="hidden" class="addon-qty-hidden" name="adicionales[conductor_extra]" value="0">
          </div>

          {{-- CARD 2: GASOLINA PREPAGO --}}
          <div class="svc-card svc-card--accent carousel-item">
            <div class="svc-top">
              <div class="svc-ico"><i class="fas fa-gas-pump"></i></div>
              <div class="svc-meta">
                <div class="svc-name">Gasolina prepago</div>
              </div>
            </div>

            <div class="svc-bottom">
              <label class="switch switch-soft">
                <input type="checkbox" id="gasolinaToggle" data-litros="0" data-costo-litro="20">
                <span class="slider"></span>
              </label>
            </div>

            <div class="svc-fields" id="gasolinaFields" style="display:none;">
              <div class="svc-total">
                <span>Total Gasolina (<span id="litrosLabel">0</span>L)</span>
                <b id="gasolinaTotal">$0.00 MXN</b>
              </div>
            </div>
            <input type="hidden" name="gasolina_prepago_valor" id="gasolinaTotalHidden" value="0">
          </div>

          {{-- CARD 3: DROP OFF --}}
          <div class="svc-card svc-card--accent dropoff-wrapper carousel-item">
            <div class="svc-top">
              <div class="svc-ico"><i class="fas fa-flag-checkered"></i></div>
              <div class="svc-meta">
                <div class="svc-name">Drop Off</div>
              </div>
            </div>

            <div class="svc-bottom">
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
                  @foreach($ubicaciones as $u)
                    <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km ?? 0 }}">
                      {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }} km)
                    </option>
                  @endforeach
                  <option value="0">Dirección personalizada</option>
                </select>
              </div>

              <div class="svc-field" id="dropGroupDireccion" style="display: none;">
                <label class="svc-label">Dirección</label>
                <input type="text" id="dropDireccion" class="input" placeholder="Calle, No, Colonia...">
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

          {{-- CARD 4: DELIVERY --}}
          <div class="svc-card svc-card--accent delivery-wrapper carousel-item"
            data-delivery-total="{{ $deliverySafe->total ?? 0 }}"
            data-costo-km="{{ $costoKmCategoriaSafe }}">

            <div class="svc-top">
              <div class="svc-ico"><i class="fas fa-truck"></i></div>
              <div class="svc-meta">
                <div class="svc-name">Delivery</div>
              </div>
            </div>

            <div class="svc-bottom">
              <label class="switch switch-soft">
                <input type="checkbox" id="deliveryToggle" {{ !empty($deliverySafe->activo) ? 'checked' : '' }}>
                <span class="slider"></span>
              </label>
            </div>

            <div class="svc-fields" id="deliveryFields" style="display: {{ !empty($deliverySafe->activo) ? 'block' : 'none' }};">
              <div class="svc-field">
                <label class="svc-label">Seleccionar ubicación</label>
                <select id="deliveryUbicacion" class="input">
                  <option value="">Seleccione...</option>
                  @foreach($ubicacionesSafe as $u)
                    <option value="{{ $u->id_ubicacion }}" data-km="{{ $u->km ?? 0 }}"
                            {{ (!empty($deliverySafe->id_ubicacion) && $deliverySafe->id_ubicacion == $u->id_ubicacion) ? 'selected' : '' }}>
                      {{ $u->estado }} - {{ $u->destino }} ({{ $u->km ?? 0 }} km)
                    </option>
                  @endforeach
                  <option value="0" {{ (isset($deliverySafe->id_ubicacion) && (int)$deliverySafe->id_ubicacion === 0) ? 'selected' : '' }}>Dirección personalizada</option>
                </select>
              </div>

              <div class="svc-field" id="groupDireccion" style="display: none;">
                <label class="svc-label">Dirección</label>
                <input type="text" id="deliveryDireccion" class="input" placeholder="Calle, No, Colonia..." value="{{ $deliverySafe->direccion ?? '' }}">
              </div>

              <div class="svc-field" id="groupKm" style="display: none;">
                <label class="svc-label">Kilómetros</label>
                <input type="number" id="deliveryKm" class="input" placeholder="0" value="{{ $deliverySafe->km ?? 0 }}">
              </div>

              <div class="svc-total">
                <span>Total Delivery</span>
                <b id="deliveryTotal">${{ number_format($deliverySafe->total ?? 0, 2) }} MXN</b>
              </div>
            </div>
            <input type="hidden" id="deliveryTotalHidden" value="{{ $deliverySafe->total ?? 0 }}">
          </div>

          {{-- CARD 5: SILLA DE BEBÉ --}}
          <div class="svc-card svc-card--addon carousel-item" data-id="silla_bebe" data-name="Silla de bebé" data-price="150" data-charge="por_dia">
            <div class="svc-top">
              <div class="svc-ico"><i class="fas fa-baby-carriage"></i></div>
              <div class="svc-meta">
                <div class="svc-name">Silla de bebé</div>
              </div>
            </div>

            <div class="svc-bottom">
              <label class="switch switch-soft">
                <input type="checkbox" class="addon-toggle" data-addon="silla_bebe">
                <span class="slider"></span>
              </label>
            </div>

            <div class="svc-addon-expanded" id="sillaBebeExpanded" style="display: none;">
              <div class="svc-price-row">
                <div class="price-label">Costo</div>
                <div class="price-value">$150 MXN <span>/ día</span></div>
              </div>

              <div class="svc-quantity-row">
                <div class="quantity-control">
                  <button class="qty-btn minus" type="button">−</button>
                  <span class="qty-value" data-qty="1">1</span>
                  <button class="qty-btn plus" type="button">+</button>
                  <span class="max-hint">Máx 3</span>
                </div>
              </div>

              <div class="svc-total-row">
                <span>Total Silla de bebé</span>
                <b class="addon-total">$150.00 MXN</b>
              </div>
            </div>
            <input type="hidden" class="addon-qty-hidden" name="adicionales[silla_bebe]" value="0">
          </div>


        </div> {{-- .carousel-track --}}
      </div> {{-- .carousel-container --}}

      <button class="carousel-arrow next" type="button" aria-label="Siguiente">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div> {{-- .adicionales-carousel --}}


  </div> {{-- .stack-body --}}
</section>

      {{-- ======================
           5) PROTECCIONES
      ======================= --}}
      <section class="stack-card acordeon-item" data-seccion="protecciones" data-siguiente="cliente">
        <div class="stack-head">
          <div class="stack-title"><i class="fas fa-shield-alt"></i> Protecciones</div>
          <div class="stack-indicator"><i class="fas fa-chevron-down"></i></div>
        </div>

        <div class="stack-body">
          <div class="picker-row">
            <button class="btn primary" type="button" id="btnProtecciones"><i class="fas fa-shield-alt"></i> Seleccionar protección</button>

            <div class="picker-selected">
              <div class="picker-label">Seleccionado</div>
              <div class="picker-value" id="proteSelTxt">— Ninguna protección —</div>
              <div class="picker-sub" id="proteSelSub">Costo se refleja en el resumen.</div>
            </div>

            <button class="btn gray" type="button" id="proteRemove" style="display:none;">✖</button>
          </div>
        </div>
      </section>

      {{-- ======================
           6) CLIENTE
======================= --}}
<section class="stack-card acordeon-item" data-seccion="cliente" data-siguiente="final">
  <div class="stack-head">
    <div class="stack-title"><i class="fas fa-user"></i> Datos del cliente</div>
    <div class="stack-indicator"><i class="fas fa-chevron-down"></i></div>
  </div>

  <div class="stack-body" id="clienteFormBody">
    <div class="form-2">
      <!-- Nombre -->
      <div>
        <label>Nombre <span style="color:#ef4444;">*</span></label>
        <input id="nombre_cliente" name="nombre_cliente" class="input" type="text" required value="{{ $cotizacion->nombre_cliente ?? '' }}">
        <div class="validation-message error" id="nombre_error">El nombre es obligatorio</div>
      </div>

      <!-- Apellidos -->
      <div>
        <label>Apellidos <span style="color:#ef4444;">*</span></label>
        <input id="apellidos_cliente" name="apellidos_cliente" class="input" type="text" required value="{{ $cotizacion->apellidos_cliente ?? '' }}">
        <div class="validation-message error" id="apellidos_error">Los apellidos son obligatorios</div>
      </div>

      <!-- Email -->
      <div>
        <label>Email <span style="color:#ef4444;">*</span></label>
        <input id="email_cliente" name="email_cliente" class="input" type="email" required value="{{ $cotizacion->email_cliente ?? '' }}">
        <div class="validation-message error" id="email_error">El email es obligatorio y debe ser válido</div>
      </div>

      <!-- Teléfono -->
      <div>
        <label>Teléfono <span style="color:#ef4444;">*</span></label>
        <div class="phone-grid" id="phoneCombo">
          <button class="phone-prefix" type="button" id="phone_toggle" aria-label="Elegir país">
            <span class="flag" id="phone_flag">🇲🇽</span>
            <span class="code" id="phone_code">+52</span>
            <span class="chev">▾</span>
          </button>
          <input id="telefono_ui" class="input" type="tel" inputmode="tel" placeholder="4421234567" required value="{{ $cotizacion->telefono_cliente ?? '' }}">
          <div class="combo-dd phone-dd" id="phone_dd" role="listbox" aria-label="Lista de ladas">
            <div class="dd-head">
              <input id="phone_search" class="dd-search" type="text" placeholder="Buscar país o lada…">
            </div>
            <div class="dd-list" id="phone_list"></div>
          </div>
        </div>
        <div class="validation-message error" id="telefono_error">El teléfono es obligatorio (mínimo 8 dígitos)</div>
      </div>

      <!-- Fila de País y Vuelo -->
      <div class="pais-vuelo-row">
        <!-- País - SIEMPRE VERDE (readonly con valor fijo) -->
        <div>
          <label>País <span style="color:#ef4444;">*</span></label>
          <input type="hidden" id="pais" name="pais" value="MÉXICO">
          <div class="input readonly-country" style="display: flex; align-items: center; gap: 0.5rem; background: transparent; border-radius: 0.5rem; padding: 0.6rem 0.75rem;">
            <span id="pais_flag_ui">🇲🇽</span>
            <span id="pais_text_ui">México</span>
          </div>
        </div>

        <!-- Vuelo - solo requerido si sucursal es Aeropuerto -->
        <div>
          <label>Vuelo <span id="vuelo_required_mark" style="display:none; color:#ef4444;">*</span> <span style="color:#6b7280;">(opcional)</span></label>
          <input id="no_vuelo" name="no_vuelo" class="input" type="text" placeholder="UA2068" value="{{ $cotizacion->no_vuelo ?? '' }}">
          <div class="validation-message error" id="vuelo_error" style="display:none;">El número de vuelo es obligatorio para sucursal Aeropuerto</div>
          <div class="small muted" style="font-size: 0.7rem; margin-top: 0.3rem; color: #666;">
            Solo requerido si la sucursal es Aeropuerto
          </div>
        </div>
      </div>
    </div>

    <!-- Comentarios - SIEMPRE VERDE (opcional) -->
    <div class="comentarios-wrapper" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
      <div class="comentarios-header">
        <span style="font-size: 1.1rem;"><i class="fas fa-comment-dots"></i> Comentarios</span>
        <span style="font-size: 0.75rem; color: #6b7280;">(Opcional)</span>
      </div>
      <textarea
        id="comentarios"
        name="comentarios"
        class="input"
        rows="4"
        placeholder="Instrucciones especiales, alergias, requerimientos específicos, etc..."
        style="margin-top: 0.5rem; resize: vertical;">{{ $cotizacion->comentarios ?? '' }}</textarea>
    </div>

    <!-- Botón -->
    <div class="acciones single" style="margin-top: 1.5rem;">
      <button class="btn primary" id="btnCotizar" type="submit"><i class="fas fa-check-circle"></i> Registrar cotización</button>
    </div>
  </div>
</section>

    </form>

  </main>
</div>

{{-- MODAL: CATEGORÍAS --}}
<div class="pop modal" id="catPop">
  <div class="box modal-box" style="max-width: 950px;">
    <header class="modal-head" style="background: var(--brand); color: #fff;">
      <div class="modal-title" style="color:#fff;">
        🚗 Selecciona una categoría
        <button type="button" id="infoCategoriasBtn" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 28px; height: 28px; margin-left: 10px; cursor: pointer; font-size: 16px;">
          <i class='bx bx-info-circle'></i>
        </button>
      </div>
      <button class="btn" id="catClose" type="button" onclick="closePop('catPop')" style="background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✖</button>
    </header>

    <div class="modal-body" style="background: #f1f5f9;">
      <div class="grid-cards" id="categoriasGrid">
        @php
          $imgCategorias = [
            1 => asset('img/aveo.png'), 2 => asset('img/virtus.png'), 3 => asset('img/jetta.png'),
            4 => asset('img/camry.png'), 5 => asset('img/renegade.png'), 6 => asset('img/taos.png'),
            7 => asset('img/avanza.png'), 8 => asset('img/Odyssey.png'), 9 => asset('img/Urvan.png'),
            10 => asset('img/Frontier.png'), 11 => asset('img/Tacoma.png'),
          ];
          $pasajeros = [ 1=>5, 2=>5, 3=>5, 4=>5, 5=>5, 6=>5, 7=>7, 8=>8, 9=>13, 10=>5, 11=>5 ];
          $transmision = [ 9 => 'Manual' ];

          $categoriasOrdenadas = $categorias->sortBy(fn($c) => (float)($c->precio_dia ?? 0))->values();
        @endphp

        @foreach($categoriasOrdenadas as $cat)
          @php
            $img = $imgCategorias[$cat->id_categoria] ?? asset('img/Logotipo.png');
            $cap = $pasajeros[$cat->id_categoria] ?? 5;
            $tran = $transmision[$cat->id_categoria] ?? 'Automático';
            $transIcon = ($tran === 'Manual' ? 'bx-joystick' : 'bx-cog');

            $featuresList = [
                ['icon' => 'bx bx-infinite', 'text' => 'Km ilimitados'],
                ['icon' => 'bx bx-shield-quarter', 'text' => 'Relevo responsabilidad'],
                ['icon' => 'bx bx-user', 'text' => "{$cap} pasajeros"],
                ['icon' => 'bx bxl-apple', 'text' => 'Apple CarPlay'],
                ['icon' => 'bx bxl-android', 'text' => 'Android Auto'],
                ['icon' => 'bx bx-wind', 'text' => 'Aire Acondicionado'],
                ['icon' => "bx {$transIcon}", 'text' => $tran],
            ];
          @endphp

          <article class="card-pick"
            data-id="{{ $cat->id_categoria }}"
            data-nombre="{{ $cat->nombre }}"
            data-precio="{{ $cat->precio_dia }}"
            data-precio-km="{{ $cat->costo_km ?? 0 }}"
            data-litros="{{ $cat->litros_maximos ?? 0 }}"
            data-img="{{ $img }}"
            data-caracteristicas='@json($featuresList)'
            >
            <div class="cp-img">
              <img src="{{ $img }}" alt="{{ $cat->nombre }}">
            </div>

            <div class="cp-left">
              <div class="cp-title">{{ $cat->nombre }}</div>
              <div class="cp-sub">{{ $cat->descripcion ?? 'Chevrolet Aveo o similar' }}</div>

              <div class="cp-meta">
                <span class="pill pill-info info-categoria-btn" style="cursor:pointer;">
                  <i class='bx bx-info-circle'></i> Características
                </span>
              </div>

              <div class="cp-meta">
                <span class="pill">Código: {{ $cat->codigo ?? ($cat->id_categoria ?? 'N/A') }}</span>
                @if(isset($cat->activo) && (int)$cat->activo === 1)
                  <span class="pill pill-ok">Activo</span>
                @endif
              </div>
            </div>

            <div class="cp-right">
              <div class="price-block">
                <div class="price-label">Tarifa base</div>
                <div class="price-value">${{ number_format((float)$cat->precio_dia, 2) }} <span>/ día</span></div>
              </div>

              <div class="price-block">
                <div class="price-label">Estimado</div>
                <div class="price-value" style="color:var(--brand)">
                    <span class="cat-estimado">$0.00</span> <span>MXN</span>
                </div>
              </div>

            <button class="btn primary btn-block" type="button" onclick="seleccionarCategoriaCotizacion(this.closest('.card-pick'))" style="margin-top:10px; border-radius: 12px; font-weight: 800; height: 45px;">Seleccionar</button>
          </article>
        @endforeach
      </div>
    </div>

    <footer class="modal-foot">
      <button class="btn gray" id="catCancel" type="button" onclick="closePop('catPop')">Cerrar</button>
    </footer>
  </div>
</div>

{{-- MODAL DE CARACTERÍSTICAS (INFO) --}}
<div class="modal-features" id="featuresModal" style="display:none;">
  <div class="modal-box">
    <div class="header">
      <h3>
        <i class='bx bx-car'></i>
        <span id="featuresCatName">Categoría</span>
      </h3>
      <button id="closeFeaturesModal">✖</button>
    </div>
    <div class="body">
      <div class="features-list" id="featuresListContainer"></div>
    </div>
  </div>
</div>

{{-- MODAL: PROTECCIONES --}}
<div class="pop modal" id="proteccionPop">
    <div class="box modal-box modal-prote-tabs">
        <header class="modal-head">
            <div class="modal-title"><i class="fas fa-lock"></i> Protecciones</div>
            <button class="btn gray" id="proteClose" type="button">✖</button>
        </header>

        <div class="tabs-bar">
            <button type="button" class="tab-btn is-active" data-tab="tab-paquetes">
                <i class="fas fa-shield-alt"></i> Paquetes
            </button>
            <button type="button" class="tab-btn" data-tab="tab-individuales">
                <i class="fas fa-puzzle-piece"></i> Individuales
            </button>
        </div>

        <div class="modal-body">
            {{-- TAB PAQUETES --}}
            <section class="tab-panel is-active" id="tab-paquetes">
                <div class="scroll-h" id="protePacksTrack">
                    <div class="loading">Cargando paquetes...</div>
                </div>
            </section>

            {{-- TAB INDIVIDUALES --}}
            <section class="tab-panel" id="tab-individuales">
                <div class="note" style="margin-bottom:14px;">Selecciona una o varias protecciones individuales.</div>

                {{-- GRUPO: COLISIÓN Y ROBO --}}
                <h4 class="cat-title">Colisión y robo</h4>
                <div class="scroll-h" id="insColisionTrack">
                    @php $colisionOrdenado = ($grupo_colision ?? collect())->sortByDesc('precio_por_dia'); @endphp
                    @forelse($colisionOrdenado as $ind)
                        <label class="ins-card individual-item"
                               data-id="{{ $ind->id_individual }}"
                               data-precio="{{ $ind->precio_por_dia }}"
                               data-descripcion="{{ $ind->descripcion }}">
                            <div class="body">
                                <div class="title-wrapper">
                                   <h4>{{ preg_replace('/\s*\([^)]*\)/', '', str_replace(['¿', '?', '¡', '!'], '', $ind->nombre)) }}</h4>
                                    <div class="info-icon-container">
                                        <span class="info-icon">i</span>
                                        <div class="tooltip-text">
                                            {{ $ind->descripcion }}
                                            <div class="tooltip-arrow"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>

                            </div>
                        </label>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                {{-- GRUPO: GASTOS MÉDICOS --}}
                <h4 class="cat-title">Gastos médicos</h4>
                <div class="scroll-h" id="insMedicosTrack">
                    @php $medicosOrdenado = ($grupo_medicos ?? collect())->sortByDesc('precio_por_dia'); @endphp
                    @forelse($medicosOrdenado as $ind)
                        <label class="ins-card individual-item"
                               data-id="{{ $ind->id_individual }}"
                               data-precio="{{ $ind->precio_por_dia }}"
                               data-descripcion="{{ $ind->descripcion }}">
                            <div class="body">
                                <div class="title-wrapper">
                                  <h4>{{ preg_replace('/\s*\([^)]*\)/', '', str_replace(['¿', '?', '¡', '!'], '', $ind->nombre)) }}</h4>
                                    <div class="info-icon-container">
                                        <span class="info-icon">i</span>
                                        <div class="tooltip-text">
                                            {{ $ind->descripcion }}
                                            <div class="tooltip-arrow"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>

                            </div>
                        </label>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                {{-- GRUPO: ASISTENCIA PARA EL CAMINO --}}
                <h4 class="cat-title">Asistencia para el camino</h4>
                <div class="scroll-h" id="insCaminoTrack">
                    @php $asistenciaOrdenado = ($grupo_asistencia ?? collect())->sortByDesc('precio_por_dia'); @endphp
                    @forelse($asistenciaOrdenado as $ind)
                        <label class="ins-card individual-item"
                               data-id="{{ $ind->id_individual }}"
                               data-precio="{{ $ind->precio_por_dia }}"
                               data-descripcion="{{ $ind->descripcion }}">
                            <div class="body">
                                <div class="title-wrapper">
                                <h4>{{ preg_replace('/\s*\([^)]*\)/', '', str_replace(['¿', '?', '¡', '!'], '', $ind->nombre)) }}</h4>
                                    <div class="info-icon-container">
                                        <span class="info-icon">i</span>
                                        <div class="tooltip-text">
                                            {{ $ind->descripcion }}
                                            <div class="tooltip-arrow"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>

                            </div>
                        </label>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                {{-- GRUPO: DAÑOS A TERCEROS --}}
                <h4 class="cat-title">Daños a terceros</h4>
                <div class="scroll-h" id="insTercerosTrack">
                    @php $tercerosOrdenado = ($grupo_terceros ?? collect())->sortByDesc('precio_por_dia'); @endphp
                    @forelse($tercerosOrdenado as $ind)
                        <label class="ins-card individual-item"
                               data-id="{{ $ind->id_individual }}"
                               data-precio="{{ $ind->precio_por_dia }}"
                               data-descripcion="{{ $ind->descripcion }}">
                            <div class="body">
                                <div class="title-wrapper">
                                   <h4>{{ preg_replace('/\s*\([^)]*\)/', '', str_replace(['¿', '?', '¡', '!'], '', $ind->nombre)) }}</h4>
                                    <div class="info-icon-container">
                                        <span class="info-icon">i</span>
                                        <div class="tooltip-text">
                                            {{ $ind->descripcion }}
                                            <div class="tooltip-arrow"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>

                            </div>
                        </label>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>

                {{-- GRUPO: PROTECCIONES AUTOMÁTICAS --}}
                <h4 class="cat-title">Protecciones automáticas</h4>
                <div class="scroll-h" id="insAutoTrack">
                    @php $autoOrdenado = ($grupo_protecciones ?? collect())->sortByDesc('precio_por_dia'); @endphp
                    @forelse($autoOrdenado as $ind)
                        <label class="ins-card individual-item"
                               data-id="{{ $ind->id_individual }}"
                               data-precio="{{ $ind->precio_por_dia }}"
                               data-descripcion="{{ $ind->descripcion }}">
                            <div class="body">
                                <div class="title-wrapper">
                                    <h4>{{ preg_replace('/\s*\([^)]*\)/', '', str_replace(['¿', '?', '¡', '!'], '', $ind->nombre)) }}</h4>
                                    <div class="info-icon-container">
                                        <span class="info-icon">i</span>
                                        <div class="tooltip-text">
                                            {{ $ind->descripcion }}
                                            <div class="tooltip-arrow"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="precio">${{ number_format($ind->precio_por_dia, 2) }} <span>MXN x Día</span></div>
                                <div class="switch switch-individual" data-id="{{ $ind->id_individual }}"></div>

                            </div>
                        </label>
                    @empty
                        <div class="muted" style="padding:10px 0; font-weight:800;">Sin opciones en esta categoría.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <footer class="modal-foot foot-split">
            <button class="btn gray" id="proteCancel" type="button">Cerrar</button>
            <button class="btn primary" id="proteApply" type="button">Aplicar</button>
        </footer>
    </div>
</div>

{{-- MODAL: RESUMEN --}}
<div class="pop modal" id="resumenPop">
  <div class="box modal-box resumen-v2">
    <header class="modal-head">
      <div class="modal-title"><i class="fas fa-file-alt"></i> Resumen de cotización</div>
      <button class="btn gray" id="resumenClose" type="button">✖</button>
    </header>
    <div class="modal-body">
      <div class="resumen-v2-card">
        <div class="rv2-section rv2-location-section">
          <h3 class="rv2-section-title">LUGAR Y FECHA</h3>
          <div class="rv2-location-two-columns">
            <div class="rv2-location-col">
              <div class="rv2-location-label"><i class="fas fa-map-marker-alt"></i> PICK-UP</div>
              <div class="rv2-location-sucursal" id="resSucursalRetiro">—</div>
              <div class="rv2-location-datetime">
                <div class="rv2-location-date"><i class="fa-regular fa-calendar"></i><span id="resFechaInicioDetail">—</span></div>
                <div class="rv2-location-time"><i class="fa-regular fa-clock"></i><span id="resHoraInicioDetail">—</span></div>
              </div>
            </div>
            <div class="rv2-location-col">
              <div class="rv2-location-label"><i class="fas fa-map-marker-alt"></i> DEVOLUCIÓN</div>
              <div class="rv2-location-sucursal" id="resSucursalEntrega">—</div>
              <div class="rv2-location-datetime">
                <div class="rv2-location-date"><i class="fa-regular fa-calendar"></i><span id="resFechaFinDetail">—</span></div>
                <div class="rv2-location-time"><i class="fa-regular fa-clock"></i><span id="resHoraFinDetail">—</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rv2-section">
          <h3 class="rv2-section-title">TU AUTO</h3>
          <div class="rv2-car">
            <div class="rv2-car-image"><img id="resCatImage" src="" alt="Auto"></div>
            <div class="rv2-car-info">
              <div class="rv2-car-name" id="resCatName">—</div>
              <div class="rv2-car-desc" id="resCatDesc">—</div>
              <div class="rv2-car-codigo" id="resCatCodigo">—</div>
              <div class="rv2-car-features" id="resCatFeatures"></div>
            </div>
          </div>
        </div>

        <div class="rv2-section">
          <h3 class="rv2-section-title">DETALLES DEL PRECIO</h3>
          <div class="rv2-price-block">
            <div class="rv2-price-title">TARIFA BASE <button id="btnEditBase" class="btn-edit-base"><i class="fas fa-pen"></i></button></div>
            <div class="rv2-price-amount" id="resBaseAmount">$0.00 MXN</div>
            <div class="rv2-price-note" id="resBaseNote">0 día(s) – precio por día $0.00 MXN</div>
          </div>
          <div class="rv2-total-row"><span>Total:</span><strong id="resBaseTotalEstilo">$0.00 MXN</strong></div>
          <div class="rv2-included">
            <div class="rv2-included-title">INCLUIDO</div>
            <ul><li><i class="fas fa-infinity"></i> Kilometraje ilimitado</li><li><i class="fas fa-shield-alt"></i> Relevo de Responsabilidad (Lí)</li></ul>
          </div>
          <div class="rv2-options" id="rv2OptionsContainer"><div class="rv2-options-title">OPCIONES DE RENTA</div><div id="rv2OptionsList"></div></div>
          <div class="rv2-section" id="proteccionesSection"><h3 class="rv2-section-title">PROTECCIONES</h3><div id="rv2ProteccionesList"></div></div>
          <div class="rv2-tax-row"><div class="rv2-tax-label">CARGOS E IVA (16%)</div><div class="rv2-tax-amount" id="resIvaEstilo">$0.00 MXN</div></div>
          <div class="rv2-grand-total"><span>Total</span><strong id="resTotalEstilo">$0.00 MXN</strong></div>
        </div>
      </div>
    </div>
    <footer class="modal-foot"><button class="btn primary" type="button" id="resumenOk">Entendido</button></footer>
  </div>
</div>

{{-- MODAL: CONFIRMACIÓN --}}
<div class="pop modal" id="confirmPop" style="display:none;">
  <div class="box modal-box">
    <header class="modal-head"><div class="modal-title"><i class="fas fa-check-circle"></i> Cotización registrada</div><button class="btn gray" id="confirmClose" type="button">✖</button></header>
    <div class="modal-body"><p style="margin:0; font-weight:800; color:#111827;">¡Listo! La cotización se registró correctamente.</p><p class="muted" style="margin:8px 0 0;">Te enviaremos a <b>Cotizaciones</b>.</p></div>
    <footer class="modal-foot"><button class="btn primary" id="confirmOk" type="button">Ir a Cotizaciones</button></footer>
  </div>
</div>

<script>
  window.cotizacionEditar = @json($cotizacion ?? null);
  window.serviciosEditar = @json($serviciosCotizacion ?? []);
  window.seguroEditar = @json($seguroCotizacion ?? null);
</script>

@endsection

@section('js-vistaCotizar')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.iconosPorId = {
            @foreach($sucursales as $ciudad => $grupo)
                @foreach($grupo as $s)
                    @php
                        $nombre = strtolower($s->sucursal);
                        $icono = 'fa-building';
                        if (str_contains($nombre, 'aeropuerto')) { $icono = 'fa-plane-departure'; }
                        elseif ((str_contains($nombre, 'central') || str_contains($nombre, 'autobuses')) && !str_contains($nombre, 'plaza central park')) { $icono = 'fa-bus'; }
                    @endphp
                    {{ $s->id_sucursal }}: '{{ $icono }}',
                @endforeach
            @endforeach
        };
    </script>

    <script src="{{ asset('js/Cotizar.js') }}"></script>
    <script>
        function closePop(popId) {
            const pop = document.getElementById(popId);
            if (pop) pop.style.display = "none";
        }
        function seleccionarCategoriaCotizacion(cardElement) {
            const id = cardElement.dataset.id;
            const nombre = cardElement.dataset.nombre || "";
            const desc = cardElement.dataset.desc || "";
            const precio = Number(cardElement.dataset.precio || 0);
            const precioKm = Number(cardElement.dataset.precioKm || 0);
            const img = cardElement.dataset.img || "";
            const capacidad = parseFloat(cardElement.dataset.litros || 0);
            if (window._cotizacionAPI && window._cotizacionAPI.setCategoria) {
                window._cotizacionAPI.setCategoria({ id, nombre, desc, precio_dia: precio, precio_km: precioKm, img, capacidad_tanque: capacidad });
            }
            const catPop = document.getElementById('catPop');
            if (catPop) catPop.style.display = "none";
        }
    </script>
@endsection
