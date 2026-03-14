@extends('layouts.Usuarios')

@section('Titulo', 'Visor Reservación')

@section('css-visorReservacion')
<style>
.bloqueado {
    pointer-events: none;
    background-color: #e9ecef;
    opacity: 1;
}
.btn-confirmar-cambios{
    background:#28a745;
    color:#fff;
    border:none;
    padding:14px 30px;
    font-size:16px;
    font-weight:600;
    border-radius:8px;
    transition:all .25s ease;
}

.btn-confirmar-cambios:hover{
    background:#218838;
    transform:translateY(-1px);
}
</style>
@endsection

@section('contenidoReservaciones')

<div class="container py-4">

{{-- ================= ALERTAS ================= --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- =================================================
CARD 1 – VEHÍCULO / CATEGORÍA / SERVICIOS
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="card" value="card1">

    <input type="hidden"
           name="id_categoria"
           id="inputCategoria"
           value="{{ $reservacion->id_categoria }}">

    <div class="card mb-4 shadow">

        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            Vehículo y servicios
            <div>
                <button type="button"
                        class="btn btn-outline-warning btn-sm"
                        id="btnEditarServicios">
                    Editar
                </button>

                <button type="submit"
                        class="btn btn-primary btn-sm d-none"
                        id="btnGuardarCard1">
                    Guardar
                </button>
            </div>
        </div>

        <div class="card-body row">

            {{-- IZQUIERDA --}}
            <div class="col-md-4 text-center">

                <img id="imgVehiculo"
                     class="img-fluid rounded mb-3"
                     src="{{ asset(
                        match($reservacion->id_categoria) {
                            1 => 'img/aveo.png',
                            2 => 'img/virtus.png',
                            3 => 'img/jetta.png',
                            4 => 'img/camry.png',
                            5 => 'img/renegade.png',
                            6 => 'img/taos.png',
                            7 => 'img/avanza.png',
                            8 => 'img/Odyssey.png',
                            9 => 'img/Hiace.png',
                            10 => 'img/Frontier.png',
                            11 => 'img/Tacoma.png',
                        }
                     ) }}">

                <h6 class="fw-bold" id="textoCategoria">
                    {{
                        match($reservacion->id_categoria) {
                            1 => 'C | Compacto',
                            2 => 'D | Mediano',
                            3 => 'E | Grande',
                            4 => 'F | Full Size',
                            5 => 'IC | SUV Compacta',
                            6 => 'I | SUV Mediana',
                            7 => 'IB | SUV Familiar Compacta',
                            8 => 'M | Minivan',
                            9 => 'L | Van Pasajeros 13',
                            10 => 'H | Pickup Doble Cabina',
                            11 => 'HI | Pickup Doble Cabina',
                        }
                    }}
                </h6>

                <button type="button"
                        class="btn btn-outline-primary btn-sm d-none mt-2"
                        id="btnCambiarCategoria">
                    Cambiar categoría
                </button>

            </div>

            {{-- DERECHA --}}
            <div class="col-md-8">

                {{-- SELECT SERVICIOS --}}
                <div class="row mb-3 d-none" id="contenedorAgregarServicio">
                    <div class="col-md-8">
                        <select id="selectServicio" class="form-select">
                            <option value="">-- Selecciona un servicio --</option>
                            @foreach($catalogoServicios as $servicio)
                                <option value="{{ $servicio->id_servicio }}"
                                        data-nombre="{{ $servicio->nombre }}"
                                        data-precio="{{ $servicio->precio }}">
                                    {{ $servicio->nombre }} (${{ number_format($servicio->precio, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button"
                                class="btn btn-success w-100"
                                id="btnConfirmarAgregar">
                            Agregar
                        </button>
                    </div>
                </div>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="tablaServicios">
                        @foreach($servicios as $i => $s)
                        <tr>
                            <td>
                                {{ $s->nombre }}
                                <input type="hidden"
                                       name="servicios[{{ $i }}][id]"
                                       value="{{ $s->id_servicio }}">
                            </td>

                            <td>
                                <input type="number"
                                       min="1"
                                       name="servicios[{{ $i }}][cantidad]"
                                       class="form-control editable-servicio"
                                       value="{{ $s->cantidad }}"
                                       readonly>
                            </td>

                            <td>
                                ${{ number_format($s->precio_unitario, 2) }}
                                <input type="hidden"
                                       name="servicios[{{ $i }}][precio]"
                                       value="{{ $s->precio_unitario }}">
                            </td>

                            <td>${{ number_format($s->cantidad * $s->precio_unitario, 2) }}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-danger d-none btnEliminarServicio">✖</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <p><strong>Subtotal:</strong> ${{ number_format($subtotal, 2) }}</p>
                <p><strong>IVA:</strong> ${{ number_format($iva, 2) }}</p>
                <p><strong>Total:</strong> ${{ number_format($total, 2) }}</p>

            </div>
        </div>
    </div>
</form>

{{-- =================================================
CARD 2 – DATOS DEL CLIENTE
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="card" value="card2">

    <div class="card mb-4 shadow">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            Datos del cliente
            <div>
                <button type="button"
                        class="btn btn-outline-warning btn-sm"
                        id="btnEditarCliente">
                    Editar
                </button>

                <button type="submit"
                        class="btn btn-primary btn-sm d-none"
                        id="btnGuardarCliente">
                    Actualizar
                </button>
            </div>
        </div>

        <div class="card-body">

            <input class="form-control mb-2 editable-cliente"
                   name="nombre_cliente"
                   value="{{ $cliente->nombre_cliente }}"
                   readonly required>

            <input class="form-control mb-2 editable-cliente"
                   type="email"
                   name="email_cliente"
                   value="{{ $cliente->email_cliente }}"
                   readonly required>

            <input class="form-control mb-2 editable-cliente"
                   name="telefono_cliente"
                   value="{{ $cliente->telefono_cliente }}"
                   readonly required>

        </div>
    </div>
</form>

{{-- =================================================
CARD 3 – ITINERARIO
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="card" value="card3">

    <div class="card mb-4 shadow">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            Itinerario
            <div>
                <button type="button"
                        class="btn btn-outline-warning btn-sm"
                        id="btnEditarItinerario">
                    Editar
                </button>

                <button type="submit"
                        class="btn btn-primary btn-sm d-none"
                        id="btnGuardarItinerario">
                    Actualizar
                </button>
            </div>
        </div>

        <div class="card-body">

            <div class="alert alert-danger d-none" id="alertCard3"></div>

            <label>Fecha retiro</label>
            <input type="date"
                   class="form-control mb-2 editable-itinerario"
                   name="fecha_inicio"
                   value="{{ $itinerario->fecha_inicio }}"
                   readonly required>

            <input type="date"
                   class="form-control mb-2 editable-itinerario"
                   name="fecha_fin"
                   value="{{ $itinerario->fecha_fin }}"
                   readonly required>

            <input type="time"
                   class="form-control mb-2 editable-itinerario"
                   name="hora_retiro"
                   value="{{ $itinerario->hora_retiro }}"
                   readonly required>

            <input type="time"
                   class="form-control mb-2 editable-itinerario"
                   name="hora_entrega"
                   value="{{ $itinerario->hora_entrega }}"
                   readonly required>

            <select name="sucursal_retiro"
                    class="form-select mb-2 editable-itinerario bloqueado">
                @foreach($sucursales as $s)
                    <option value="{{ $s->id_sucursal }}"
                        @selected($itinerario->sucursal_retiro == $s->id_sucursal)>
                        {{ $s->nombre_mostrado }}
                    </option>
                @endforeach
            </select>

            <select name="sucursal_entrega"
                    class="form-select mb-3 editable-itinerario bloqueado">
                @foreach($sucursales as $s)
                    <option value="{{ $s->id_sucursal }}"
                        @selected($itinerario->sucursal_entrega == $s->id_sucursal)>
                        {{ $s->nombre_mostrado }}
                    </option>
                @endforeach
            </select>

        </div>
    </div>
</form>
<div class="text-center my-5">

    <form method="POST" action="{{ route('visor.reenviarCorreo', $reservacion->id_reservacion) }}">
        @csrf

        <button type="submit" class="btn-confirmar-cambios">
            Confirmar cambios y reenviar correo
        </button>

    </form>

</div>
</div>

{{-- ================= MODAL CATEGORÍA ================= --}}
@php
    $predeterminados = [
        'C'  => ['pax'=>5,  'small'=>2, 'big'=>1],
        'D'  => ['pax'=>5,  'small'=>2, 'big'=>1],
        'E'  => ['pax'=>5,  'small'=>2, 'big'=>2],
        'F'  => ['pax'=>5,  'small'=>2, 'big'=>2],
        'IC' => ['pax'=>5,  'small'=>2, 'big'=>2],
        'I'  => ['pax'=>5,  'small'=>3, 'big'=>2],
        'IB' => ['pax'=>7,  'small'=>3, 'big'=>2],
        'M'  => ['pax'=>7,  'small'=>4, 'big'=>2],
        'L'  => ['pax'=>13, 'small'=>4, 'big'=>3],
        'H'  => ['pax'=>5,  'small'=>3, 'big'=>2],
        'HI' => ['pax'=>5,  'small'=>3, 'big'=>2],
    ];

    if (!function_exists('img_por_categoria_modal')) {
        function img_por_categoria_modal($codigo) {
            switch ($codigo) {
                case 'C':  return asset('img/aveo.png');
                case 'D':  return asset('img/virtus.png');
                case 'E':  return asset('img/jetta.png');
                case 'F':  return asset('img/camry.png');
                case 'IC': return asset('img/renegade.png');
                case 'I':  return asset('img/taos.png');
                case 'IB': return asset('img/avanza.png');
                case 'M':  return asset('img/Odyssey.png');
                case 'L':  return asset('img/Hiace.png');
                case 'H':  return asset('img/Frontier.png');
                case 'HI': return asset('img/Tacoma.png');
                default:   return asset('img/Logotipo.png');
            }
        }
    }

    $ordenCategoriasModal = ['C','D','E','F','IC','I','IB','H','HI','L','M'];

    $categoriasModal = collect($categoriasCards ?? [])->sortBy(function($item) use ($ordenCategoriasModal) {
        return array_search($item->codigo, $ordenCategoriasModal);
    });
@endphp

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecciona categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    @forelse ($categoriasModal as $cat)
                        @php
                            $codigo = $cat->codigo;
                            $cap    = $predeterminados[$codigo] ?? ['pax'=>5,'small'=>2,'big'=>1];
                            $img    = img_por_categoria_modal($codigo);
                            $textoCategoria = $codigo . ' | ' . $cat->nombre;
                        @endphp

                        <div class="col-md-6">
                            <article class="car-card catalog-group h-100">
                                @if(!empty($cat->descuento_miembro) && $cat->descuento_miembro > 0)
                                    <span class="offer-badge">-{{ (int) round($cat->descuento_miembro) }}%</span>
                                @endif

                                <header class="car-title">
                                    <h3>{{ strtoupper($cat->nombre) }}</h3>
                                    <p>{{ $cat->descripcion }} | {{ $codigo }}</p>
                                </header>

                                <div class="car-media">
                                    <img src="{{ $img }}" alt="{{ $cat->nombre }}">
                                </div>

                                <ul class="car-specs">
                                    <li><i class="fa-solid fa-user-large"></i> {{ $cap['pax'] }}</li>
                                    <li><i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }}</li>
                                    <li><i class="fa-solid fa-briefcase"></i> {{ $cap['big'] ?? 1 }}</li>

                                    <li title="Transmisión">
                                        <span class="spec-letter">
                                            T | {{ $codigo === 'L' ? 'Estándar' : 'Automática' }}
                                        </span>
                                    </li>

                                    <li title="Aire acondicionado">
                                        <i class="fa-regular fa-snowflake"></i> A/C
                                    </li>
                                </ul>

                                <div class="car-connect">
                                    <span class="badge-chip badge-apple" title="Apple CarPlay">
                                        <span class="icon-badge">
                                            <i class="fa-brands fa-apple"></i>
                                        </span>
                                        CarPlay
                                    </span>

                                    <span class="badge-chip badge-android" title="Android Auto">
                                        <span class="icon-badge">
                                            <i class="fa-brands fa-android"></i>
                                        </span>
                                        Android Auto
                                    </span>
                                </div>

                                <button type="button"
                                        class="car-cta elegirCategoria"
                                        data-id="{{ $cat->id_categoria }}"
                                        data-texto="{{ $textoCategoria }}"
                                        data-img="{{ $img }}">
                                    Elegir
                                </button>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <h5 class="mb-0">No hay categorías disponibles</h5>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-visorReservacion')
<script src="{{ asset('js/visorReservacion.js') }}" defer></script>
@endsection
