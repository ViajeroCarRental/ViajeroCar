@extends('layouts.Ventas')

@section('Titulo', 'Checklist – Entrega y Recepción')

{{-- CSS SOLO VISUAL --}}
@section('css-vistaFacturar')
<link rel="stylesheet" href="{{ asset('css/checklist2.css') }}">
@endsection

@section('contenidoChecklist2')

<form method="POST"
          action="{{ route('checklist2.confirmarCambio', $contrato->id_contrato) }}"
          enctype="multipart/form-data">

        @csrf


    <div class="checklist2-container"
     id="checklist2-root"
     data-url-guardar-dano="{{ route('checklist2.guardarDano', ['id' => $contrato->id_contrato]) }}"
     data-url-eliminar-dano-base="{{ route('checklist2.eliminarDano', ['id' => '__ID__']) }}"
     data-url-vehiculos-categoria-base="{{ route('checklist2.vehiculosPorCategoria', ['id' => $contrato->id_contrato, 'idCategoria' => '__CAT__']) }}"
     data-url-set-vehiculo-nuevo="{{ route('checklist2.setVehiculoNuevo', ['id' => $contrato->id_contrato]) }}">


        <!-- ENCABEZADO -->
        <header class="cl2-header">

            <div class="cl2-logo">
                <img src="/img/Logotipo Fondo.jpg" alt="Viajero Car Rental">
            </div>

            <div class="cl2-title-block">
                <h1>VIAJERO CAR RENTAL</h1>
                <h2>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</h2>

                <p class="office-info">
                    BUGAMBILIAS #7, LOS BENITOS, COLÓN<br>
                    QUERÉTARO, Qro. CP 76259<br>
                    gerencia-mkt@viajerocar-rental.com<br>
                    Tel. 441 690 09 98 / Cel. 442 716 97 93
                </p>
            </div>

            <div class="cl2-ra-box">
                <div class="label">No. Rental Agreement</div>
                <div class="value">-----</div>

                <div class="label small">Fecha de Cambio</div>
                <div class="value small">--/--/---- --:--</div>
            </div>

        </header>

        <!-- COLUMNAS PRINCIPALES -->
        <section class="cl2-columns">

            <!-- COLUMNA IZQUIERDA – AUTO RECIBIDO POR EMPRESA -->
            <div class="cl2-col">
                <h3 class="cl2-section-title">AUTO RECIBIDO POR EMPRESA</h3>

                <table class="cl2-table">
                    <tr>
                        <th>CATEGORIA</th>
                        <td>{{ $categoria->codigo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>TIPO</th>
                        <td>{{ $vehiculo->tipo_servicio ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>MODELO</th>
                        <td>{{ $vehiculo->modelo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>PLACAS</th>
                        <td>{{ $vehiculo->placa ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>TRANSMISIÓN</th>
                        <td>{{ $vehiculo->transmision ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>FUEL OUT</th>
                        <td>
                            @if(!is_null($vehiculo->gasolina_actual ?? null))
                                {{ $vehiculo->gasolina_actual }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>KILOMETRAJE OUT</th>
                        <td>{{ $vehiculo->kilometraje ?? 'N/A' }}</td>
                    </tr>
                </table>

                {{-- DIAGRAMA INTERACTIVO – EMPRESA --}}
                <div class="cl2-car-diagram">
                    <div class="cl2-car-svg-box">
                        <svg class="car-svg" viewBox="0 0 800 1280" data-context="empresa">
                            <image href="{{ asset('img/diagrama-carro-danos3.png') }}"
                                   x="0" y="0" width="800" height="1280" />

                            {{-- DEFENSA DELANTERA --}}
                            <circle class="point-dot" data-zone="1" cx="400" cy="120" r="26" />
                            <circle class="point-dot" data-zone="2" cx="400" cy="210" r="26" />

                            {{-- COFRE / PARABRISAS --}}
                            <circle class="point-dot" data-zone="5" cx="400" cy="365" r="26" />

                            {{-- COSTADOS FRONTALES --}}
                            <circle class="point-dot" data-zone="3" cx="155" cy="385" r="26" />
                            <circle class="point-dot" data-zone="4" cx="645" cy="385" r="26" />

                            {{-- PUERTAS DELANTERAS --}}
                            <circle class="point-dot" data-zone="6" cx="155" cy="525" r="26" />
                            <circle class="point-dot" data-zone="7" cx="645" cy="525" r="26" />

                            {{-- PUERTAS TRASERAS --}}
                            <circle class="point-dot" data-zone="8" cx="155" cy="685" r="26" />
                            <circle class="point-dot" data-zone="9" cx="645" cy="685" r="26" />

                            {{-- TECHO --}}
                            <circle class="point-dot" data-zone="10" cx="400" cy="640" r="26" />

                            {{-- COSTADOS TRASEROS --}}
                            <circle class="point-dot" data-zone="11" cx="155" cy="845" r="26" />
                            <circle class="point-dot" data-zone="12" cx="645" cy="845" r="26" />

                            {{-- DEFENSA TRASERA --}}
                            <circle class="point-dot" data-zone="13" cx="400" cy="1010" r="26" />

                            {{-- LLANTAS --}}
                            <circle class="point-dot" data-zone="15" cx="117"  cy="458" r="26" />
                            <circle class="point-dot" data-zone="16" cx="682"  cy="458" r="26" />
                            <circle class="point-dot" data-zone="17" cx="117"  cy="908" r="26" />
                            <circle class="point-dot" data-zone="18" cx="682"  cy="908" r="26" />
                        </svg>
                    </div>

                    <p class="cl2-car-hint">
    Haz clic en los puntos para registrar daños al recibir el vehículo.
</p>

<div class="cl2-extra-block">
    <button type="button"
            id="btnMismosDaniosEmpresa"
            class="cl2-same-damage-btn"
            style="
                margin-top: 8px;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                border: 1px solid #16A34A;
                background: #F0FDF4;
                color: #166534;
                cursor: pointer;
                display: inline-block;
            ">
        ✅ Son los mismos daños del checklist
    </button>
</div>

<table class="cl2-danos-table" data-context="empresa">

                        <thead>
                            <tr>
                                <th>Zona</th>
                                <th>Daño / Nota</th>
                                <th>Costo</th>
                                <th>Foto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $tieneDaniosEmpresa = isset($danosEmpresa) && $danosEmpresa->count() > 0;
                            @endphp

                            @if($tieneDaniosEmpresa)
                                @foreach($danosEmpresa as $dano)
                                    <tr class="cl2-dano-row"
                                        data-contexto="empresa"
                                        data-zona="{{ $dano->zona }}"
                                        data-costo="{{ $dano->costo_estimado ?? 0 }}">
                                        <td>{{ $dano->zona }}</td>
                                        <td>{{ $dano->tipo_dano ?? $dano->comentario ?? '—' }}</td>
                                        <td>${{ number_format($dano->costo_estimado ?? 0, 2) }}</td>
                                        <td>Foto cargada</td>
                                        <td>
                                            <button type="button"
                                                    class="cl2-dano-delete"
                                                    data-id="{{ $dano->id_foto_cambio }}"
                                                    data-contexto="empresa">
                                                ✕
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="cl2-danos-empty">
                                    <td colspan="5">Sin daños registrados.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @php
                        $totalEmpresa = isset($danosEmpresa) ? $danosEmpresa->sum('costo_estimado') : 0;
                    @endphp

                    <div class="cl2-danos-total" data-context="empresa">
                        Total daños: ${{ number_format($totalEmpresa, 2) }} MXN
                    </div>
                </div>

                {{-- FIRMA ASESOR --}}
<div class="cl2-sign-box">
    <span>FIRMA ASESOR</span>

    @if($contrato->firma_arrendador)
        {{-- Ya hay firma, NO dibujamos la línea --}}
        <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
    @else
        {{-- No hay firma, mostramos solo la línea para firmar a mano --}}
        <div class="line"></div>
    @endif

    <span class="name" style="margin-top: 4px; display:block;">
        @if(isset($asesor))
            {{ $asesor->nombres }} {{ $asesor->apellidos }}
        @else
            NOMBRE DEL ASESOR
        @endif
    </span>
</div>


            </div>

            <!-- COLUMNA DERECHA – AUTO ENTREGADO A CLIENTE -->
            <div class="cl2-col">
                <h3 class="cl2-section-title">AUTO ENTREGADO A CLIENTE</h3>

                <table class="cl2-table" id="tablaAutoCliente">
                    <tr>
                        <th>CATEGORIA</th>
                        <td>
                            <select id="categoriaCliente"
                                    name="categoria_cliente"
                                    class="cl2-select-categoria">
                                <option value="">Selecciona una categoría…</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria }}">
                                        {{ $cat->codigo }} – {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>TIPO</th>
                        <td><span id="cliente-tipo">N/A</span></td>
                    </tr>
                    <tr>
                        <th>MODELO</th>
                        <td><span id="cliente-modelo">N/A</span></td>
                    </tr>
                    <tr>
                        <th>PLACAS</th>
                        <td><span id="cliente-placas">N/A</span></td>
                    </tr>
                    <tr>
                        <th>TRANSMISIÓN</th>
                        <td><span id="cliente-transmision">N/A</span></td>
                    </tr>
                    <tr>
                        <th>FUEL OUT</th>
                        <td><span id="cliente-fuel">N/A</span></td>
                    </tr>
                    <tr>
                        <th>KILOMETRAJE OUT</th>
                        <td><span id="cliente-km">N/A</span></td>
                    </tr>
                </table>

{{-- Hidden para tener el id del nuevo vehículo en el DOM (por si lo necesitas en otros flujos) --}}
<input type="hidden" id="idVehiculoNuevoSeleccionado" name="id_vehiculo_nuevo" value="">



                {{-- DIAGRAMA INTERACTIVO – CLIENTE --}}
                <div class="cl2-car-diagram">
                    <div class="cl2-car-svg-box">
                        <svg class="car-svg" viewBox="0 0 800 1280" data-context="cliente">
                            <image href="{{ asset('img/diagrama-carro-danos3.png') }}"
                                   x="0" y="0" width="800" height="1280" />

                            {{-- DEFENSA DELANTERA --}}
                            <circle class="point-dot" data-zone="1" cx="400" cy="120" r="26" />
                            <circle class="point-dot" data-zone="2" cx="400" cy="210" r="26" />

                            {{-- COFRE / PARABRISAS --}}
                            <circle class="point-dot" data-zone="5" cx="400" cy="365" r="26" />

                            {{-- COSTADOS FRONTALES --}}
                            <circle class="point-dot" data-zone="3" cx="155" cy="385" r="26" />
                            <circle class="point-dot" data-zone="4" cx="645" cy="385" r="26" />

                            {{-- PUERTAS DELANTERAS --}}
                            <circle class="point-dot" data-zone="6" cx="155" cy="525" r="26" />
                            <circle class="point-dot" data-zone="7" cx="645" cy="525" r="26" />

                            {{-- PUERTAS TRASERAS --}}
                            <circle class="point-dot" data-zone="8" cx="155" cy="685" r="26" />
                            <circle class="point-dot" data-zone="9" cx="645" cy="685" r="26" />

                            {{-- TECHO --}}
                            <circle class="point-dot" data-zone="10" cx="400" cy="640" r="26" />

                            {{-- COSTADOS TRASEROS --}}
                            <circle class="point-dot" data-zone="11" cx="155" cy="845" r="26" />
                            <circle class="point-dot" data-zone="12" cx="645" cy="845" r="26" />

                            {{-- DEFENSA TRASERA --}}
                            <circle class="point-dot" data-zone="13" cx="400" cy="1010" r="26" />

                            {{-- LLANTAS --}}
                            <circle class="point-dot" data-zone="15" cx="117"  cy="458" r="26" />
                            <circle class="point-dot" data-zone="16" cx="682"  cy="458" r="26" />
                            <circle class="point-dot" data-zone="17" cx="117"  cy="908" r="26" />
                            <circle class="point-dot" data-zone="18" cx="682"  cy="908" r="26" />
                        </svg>
                    </div>

                    <p class="cl2-car-hint">
    Haz clic en los puntos para registrar daños al entregar el vehículo al cliente.
</p>

<div class="cl2-extra-block">
    <div class="cl2-photo-block">
        <label class="cl2-photo-label">Fotografías del Auto — CAMBIO</label>
        <div class="cl2-photo-uploader" id="uploaderCambioAuto" data-name="fotosCambio">
            <div class="cl2-photo-msg">
                Toca para cámara o galería (JPG/PNG)
            </div>
            <input
                type="file"
                name="fotos_cambio[]"
                id="inputFotosCambio"
                accept="image/jpeg,image/png"
                multiple
            >
        </div>

        <div class="cl2-photo-preview" id="preview-fotosCambio"></div>
    </div>
</div>

<table class="cl2-danos-table" data-context="cliente">

                        <thead>
                            <tr>
                                <th>Zona</th>
                                <th>Daño / Nota</th>
                                <th>Costo</th>
                                <th>Foto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $tieneDaniosCliente = isset($danosCliente) && $danosCliente->count() > 0;
                            @endphp

                            @if($tieneDaniosCliente)
                                @foreach($danosCliente as $dano)
                                    <tr class="cl2-dano-row"
                                        data-contexto="cliente"
                                        data-zona="{{ $dano->zona }}"
                                        data-costo="{{ $dano->costo_estimado ?? 0 }}">
                                        <td>{{ $dano->zona }}</td>
                                        <td>{{ $dano->tipo_dano ?? $dano->comentario ?? '—' }}</td>
                                        <td>${{ number_format($dano->costo_estimado ?? 0, 2) }}</td>
                                        <td>Foto cargada</td>
                                        <td>
                                            <button type="button"
                                                    class="cl2-dano-delete"
                                                    data-id="{{ $dano->id_foto_cambio }}"
                                                    data-contexto="cliente">
                                                ✕
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="cl2-danos-empty">
                                    <td colspan="5">Sin daños registrados.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    @php
                        $totalCliente = isset($danosCliente) ? $danosCliente->sum('costo_estimado') : 0;
                    @endphp

                    <div class="cl2-danos-total" data-context="cliente">
                        Total daños: ${{ number_format($totalCliente, 2) }} MXN
                    </div>

                </div>

               {{-- FIRMA CLIENTE --}}
<div class="cl2-sign-box">
    <span>FIRMA CLIENTE</span>

    @if($contrato->firma_cliente)
        <img src="{{ $contrato->firma_cliente }}" class="firma-img">
    @else
        <div class="line"></div>
    @endif

    <span class="name" style="margin-top: 4px; display:block;">
        @if(!empty($reservacion->nombre_cliente) || !empty($reservacion->apellidos_cliente))
            {{ $reservacion->nombre_cliente }} {{ $reservacion->apellidos_cliente }}
        @else
            NOMBRE DEL CLIENTE
        @endif
    </span>
</div>


            </div>

        </section>

        {{-- 🔘 BOTÓN FINAL: ENVIAR CAMBIO DE AUTO --}}
        <div class="cl2-actions" style="margin-top: 20px; text-align: right;">
            <button type="submit" class="cl2-btn-confirmar" style="
                background:#E50914;
                color:#fff;
                padding:10px 18px;
                border-radius:10px;
                border:none;
                font-weight:600;
                cursor:pointer;
            ">
                Enviar cambio de auto
            </button>
        </div>
        </form> {{-- cierre del form que abrimos arriba --}}

    </div>

    {{-- ================== MODAL GLOBAL PARA DAÑOS ================== --}}
    <div id="modalDano">
        <div class="box">
            <h4 id="modalZonaLabel">Zona</h4>
            <div class="sub" id="modalContextoLabel">Contexto</div>

            <label for="tipoDano">Tipo de daño</label>
            <input type="text" id="tipoDano" placeholder="Ej. Golpe leve, rayón, cristal estrellado...">

            <label for="comentarioDano">Comentario</label>
            <textarea id="comentarioDano" placeholder="Describe el daño o alguna observación relevante..."></textarea>

            <label for="costoDano">Costo estimado (MXN)</label>
            <input type="number" id="costoDano" min="0" step="0.01" placeholder="0.00">

            <label for="fotoDano">Fotografía del daño (obligatoria)</label>

            <div class="uploader" data-name="fotoDano">
                <div class="msg">Toca para cámara o galería (JPG/PNG)</div>
                <input
                    id="fotoDano"
                    type="file"
                    accept="image/jpeg,image/png"
                >
            </div>

            <div class="preview" id="preview-fotoDano">
                <img id="previewFotoDano" alt="Vista previa del daño">
            </div>

            <button type="button" id="guardarDano" class="btn-modal btn-save">Guardar daño</button>
            <button type="button" id="cancelarDano" class="btn-modal btn-cancel">Cancelar</button>
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


    @section('js-vistaChecklist2')
        <script src="{{ asset('js/checklist2.js') }}"></script>
    @endsection

@endsection
