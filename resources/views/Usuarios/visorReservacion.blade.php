@extends('layouts.Usuarios')

@section('Titulo', 'Visor Reservación')

@section('css-visorReservaciones')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/visorReservaciones.css') }}">
@endsection

@section('contenidoReservaciones')

    <div class="container visor-reservacion py-4">

        <div class="visor-grid-top">

            {{-- CARD 2 – DATOS DEL CLIENTE --}}
            <form method="POST" action="{{ route('visor.update', $reservacion->id_reservacion) }}"
                class="visor-card visor-card-cliente">
                @csrf
                @method('PUT')

                <input type="hidden" name="card" value="card2">

                <div class="card visor-panel h-100">
                    <div class="card-header visor-card-header">
                        <div class="visor-card-title">
                            <span class="visor-pill">Cliente</span>
                            <h5 class="mb-0">Datos del cliente</h5>
                        </div>

                        <div class="visor-actions">
                            <button type="button" class="btn btn-outline-warning btn-sm"
                                id="btnEditarCliente">Editar</button>
                            <button type="submit" class="btn btn-primary btn-sm d-none"
                                id="btnGuardarCliente">Actualizar</button>
                        </div>
                    </div>

                    <div class="card-body visor-card-body">

                        @if (session('error'))
                            <div class="visor-alert alert-danger">
                                <span class="alert-icon">✕</span>
                                <span class="alert-text"><strong>{{ session('error') }}</strong></span>
                            </div>
                        @endif
                        @if (session('success') && str_contains(session('success'), 'cliente'))
                            <div class="visor-alert alert-success" id="alertaCliente">
                                <span class="alert-icon">✓</span>
                                <span class="alert-text"><strong>{{ session('success') }}</strong></span>
                            </div>
                        @else
                            <div class="visor-alert alert-success hidden" id="alertaCliente">
                                <span class="alert-icon">✓</span>
                                <span class="alert-text"><strong>Datos del cliente actualizados</strong></span>
                            </div>
                        @endif

                        @if ($errors->any() && session('card') == 'card2')
                            <div class="visor-alert alert-danger">
                                <span class="alert-icon">✕</span>
                                <span class="alert-text">
                                    <strong>Error al actualizar</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <div class="vr-float vr-float-icon">
                            <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg></span>
                            <input class="form-control editable-cliente" name="nombre_cliente"
                                value="{{ $cliente->nombre_cliente }}" placeholder=" " readonly required>
                            <label>Nombre completo</label>
                        </div>

                        <div class="vr-float vr-float-icon">
                            <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                    <path d="m22 7-10 6L2 7" />
                                </svg></span>
                            <input class="form-control editable-cliente" type="email" name="email_cliente"
                                value="{{ $cliente->email_cliente }}" placeholder=" " readonly required>
                            <label>Correo electrónico</label>
                        </div>

                        <div class="vr-float vr-float-icon">
                            <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg></span>
                            <input class="form-control editable-cliente" name="telefono_cliente"
                                value="{{ $cliente->telefono_cliente }}" placeholder=" " readonly required>
                            <label>Teléfono</label>
                        </div>
                    </div>
                </div>
            </form>

            {{-- CARD 3 – ITINERARIO --}}
            <form method="POST" action="{{ route('visor.update', $reservacion->id_reservacion) }}"
                class="visor-card visor-card-itinerario">
                @csrf
                @method('PUT')

                <input type="hidden" name="card" value="card3">

                <div class="card visor-panel h-100">
                    <div class="card-header visor-card-header">
                        <div class="visor-card-title">
                            <span class="visor-pill">Ruta</span>
                            <h5 class="mb-0">Itinerario</h5>
                        </div>

                        <div class="visor-actions">
                            <button type="button" class="btn btn-outline-warning btn-sm"
                                id="btnEditarItinerario">Editar</button>
                            <button type="submit" class="btn btn-primary btn-sm d-none"
                                id="btnGuardarItinerario">Actualizar</button>
                        </div>
                    </div>

                    <div class="card-body visor-card-body">
                        @if (session('success') &&
                                (str_contains(session('success'), 'Fechas') || str_contains(session('success'), 'sucursales')))
                            <div class="visor-alert alert-success" id="alertaItinerario">
                                <span class="alert-icon">✓</span>
                                <span class="alert-text"><strong>{{ session('success') }}</strong></span>
                            </div>
                        @else
                            <div class="visor-alert alert-success hidden" id="alertaItinerario">
                                <span class="alert-icon">✓</span>
                                <span class="alert-text"><strong>Fechas y sucursales actualizadas</strong></span>
                            </div>
                        @endif

                        @if ($errors->any() && session('card') == 'card3')
                            <div class="visor-alert alert-danger">
                                <span class="alert-icon">✕</span>
                                <span class="alert-text">
                                    <strong>Error al actualizar</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <div class="alert alert-danger d-none" id="alertCard3"></div>

                        <div class="vr-itin-grid">

                            <div class="vr-itin-col">
                                <div class="vr-itin-head">
                                    <span class="vr-itin-pin"><svg viewBox="0 0 24 24">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg></span>
                                    <span class="vr-itin-tag">Pick-up</span>
                                </div>

                                <div class="vr-float vr-float-select">
                                    <select name="sucursal_retiro" class="form-select editable-itinerario bloqueado">
                                        @php
                                            $retiroPermitidas = [
                                                'Querétaro Aeropuerto',
                                                'Querétaro Central de Autobuses',
                                                'Querétaro Oficina Plaza Central Park',
                                            ];
                                        @endphp

                                        @foreach ($sucursalesPorEstado as $estado => $lista)
                                            @php
                                                $listaRetiro = collect($lista)->filter(
                                                    fn($s) => in_array($s->nombre_sucursal, $retiroPermitidas),
                                                );
                                            @endphp

                                            @if ($listaRetiro->count())
                                                <optgroup label="{{ $estado }}">
                                                    @foreach ($listaRetiro as $s)
                                                        <option value="{{ $s->id_sucursal }}"
                                                            @selected($itinerario->sucursal_retiro == $s->id_sucursal)>
                                                            {{ $s->nombre_sucursal }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    <label>Sucursal de retiro</label>
                                </div>

                                <div class="vr-float vr-float-icon vr-always">
                                    <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg></span>
                                    <input type="text" class="form-control editable-itinerario" name="fecha_inicio"
                                        value="{{ $itinerario->fecha_inicio }}" placeholder=" " readonly required>
                                    <label>Fecha de retiro</label>
                                </div>

                                <div class="vr-float vr-float-icon vr-always">
                                    <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg></span>
                                    <input type="time" class="form-control editable-itinerario" name="hora_retiro"
                                        value="{{ $itinerario->hora_retiro }}" placeholder=" " readonly required>
                                    <label>Hora de retiro</label>
                                </div>
                            </div>

                            <div class="vr-itin-col">
                                <div class="vr-itin-head">
                                    <span class="vr-itin-pin"><svg viewBox="0 0 24 24">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg></span>
                                    <span class="vr-itin-tag">Devolución</span>
                                </div>

                                <div class="vr-float vr-float-select">
                                    <select name="sucursal_entrega" class="form-select editable-itinerario bloqueado">
                                        @foreach ($sucursalesPorEstado as $estado => $lista)
                                            <optgroup label="{{ $estado }}">
                                                @foreach ($lista as $s)
                                                    <option value="{{ $s->id_sucursal }}" @selected($itinerario->sucursal_entrega == $s->id_sucursal)>
                                                        {{ $s->nombre_sucursal }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <label>Sucursal de entrega</label>
                                </div>

                                <div class="vr-float vr-float-icon vr-always">
                                    <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg></span>
                                    <input type="text" class="form-control editable-itinerario" name="fecha_fin"
                                        value="{{ $itinerario->fecha_fin }}" placeholder=" " readonly required>
                                    <label>Fecha de entrega</label>
                                </div>

                                <div class="vr-float vr-float-icon vr-always">
                                    <span class="vr-in-icon"><svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg></span>
                                    <input type="time" class="form-control editable-itinerario" name="hora_entrega"
                                        value="{{ $itinerario->hora_entrega }}" placeholder=" " readonly required>
                                    <label>Hora de entrega</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        {{-- CARD 1 – VEHÍCULO / CATEGORÍA / SERVICIOS --}}
        <form method="POST" action="{{ route('visor.update', $reservacion->id_reservacion) }}"
            class="visor-card visor-card-bottom">
            @csrf
            @method('PUT')

            <input type="hidden" name="card" value="card1">

            <input type="hidden" name="id_categoria" id="inputCategoria" value="{{ $reservacion->id_categoria }}">

            <div class="card visor-panel visor-panel-wide">
                <div class="card-header visor-card-header">
                    <div class="visor-card-title">
                        <span class="visor-pill">Reserva</span>
                        <h5 class="mb-0">Vehículo y servicios</h5>
                    </div>

                    <div class="visor-actions">
                        <button type="button" class="btn btn-outline-warning btn-sm" id="btnEditarServicios">
                            Editar
                        </button>

                        <button type="submit" class="btn btn-primary btn-sm d-none" id="btnGuardarCard1">
                            Guardar
                        </button>
                    </div>
                </div>

                <div class="card-body visor-card-body">

                    @if (session('success') &&
                            (str_contains(session('success'), 'Vehículo') ||
                                str_contains(session('success'), 'servicios') ||
                                str_contains(session('success'), 'categoría')))
                        <div class="visor-alert alert-success" id="alertaVehiculo">
                            <span class="alert-icon">✓</span>
                            <span class="alert-text"><strong>{{ session('success') }}</strong></span>
                        </div>
                    @else
                        <div class="visor-alert alert-success hidden" id="alertaVehiculo">
                            <span class="alert-icon">✓</span>
                            <span class="alert-text"><strong>Vehículo y servicios actualizados</strong></span>
                        </div>
                    @endif

                    <div class="visor-alert alert-success hidden" id="alertaServicioAgregado">
                        <span class="alert-icon">✓</span>
                        <span class="alert-text"><strong>Servicio agregado correctamente</strong></span>
                    </div>



                    <div class="visor-alert alert-success hidden" id="alertaCategoriaCambiada">
                        <span class="alert-icon">✓</span>
                        <span class="alert-text"><strong>Categoría cambiada correctamente</strong></span>
                    </div>

                    <div class="visor-alert alert-success hidden" id="alertaServicioEliminado">
                        <span class="alert-icon">✓</span>
                        <span class="alert-text"><strong>Servicio eliminado correctamente</strong></span>
                    </div>

                    @if ($errors->any() && session('card') == 'card1')
                        <div class="visor-alert alert-danger">
                            <span class="alert-icon">✕</span>
                            <span class="alert-text">
                                <strong>Error al actualizar</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </span>
                        </div>
                    @endif

                    <div class="visor-bottom-grid">

                        <div class="visor-vehicle-box">
                            <div class="visor-vehicle-media">
                                <img id="imgVehiculo" class="img-fluid rounded mb-3"
                                    src="{{ asset(
                                        match ($reservacion->id_categoria) {
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
                                        },
                                    ) }}">
                            </div>

                            <h6 class="fw-bold visor-category-name" id="textoCategoria">
                                {{ match ($reservacion->id_categoria) {
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
                                } }}
                            </h6>

                            <button type="button" class="btn btn-outline-primary btn-sm d-none mt-2"
                                id="btnCambiarCategoria">
                                Cambiar categoría
                            </button>
                        </div>

                        <div class="visor-services-box">

                            <div class="row g-2 mb-3 d-none" id="contenedorAgregarServicio">
                                <div class="col-md-8">
                                    <select id="selectServicio" class="form-select">
                                        <option value="">-- Selecciona un servicio --</option>
                                        @foreach ($catalogoServicios as $servicio)
                                            <option value="{{ $servicio->id_servicio }}"
                                                data-nombre="{{ $servicio->nombre }}"
                                                data-precio="{{ $servicio->precio }}">
                                                {{ $servicio->nombre }} (${{ number_format($servicio->precio, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <button type="button" class="btn btn-success w-100" id="btnConfirmarAgregar">
                                        Agregar
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive visor-table-wrap">
                                <table class="table table-sm align-middle visor-table">
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
                                        @forelse ($servicios as $i => $s)
                                            <tr>
                                                <td>
                                                    {{ $s->nombre }}
                                                    <input type="hidden" name="servicios[{{ $i }}][id]"
                                                        value="{{ $s->id_servicio }}">
                                                </td>

                                                <td>
                                                    <input type="number" min="1"
                                                        name="servicios[{{ $i }}][cantidad]"
                                                        class="form-control editable-servicio"
                                                        value="{{ $s->cantidad }}" readonly>
                                                </td>

                                                <td>
                                                    ${{ number_format($s->precio_unitario, 2) }}
                                                    <input type="hidden" name="servicios[{{ $i }}][precio]"
                                                        value="{{ $s->precio_unitario }}">
                                                </td>

                                                <td>${{ number_format($s->cantidad * $s->precio_unitario, 2) }}</td>

                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger d-none btnEliminarServicio">
                                                        ✖
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="fila-sin-servicios">
                                                <td>—</td>
                                                <td>—</td>
                                                <td>—</td>
                                                <td>—</td>
                                                <td>—</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="visor-totals">
                                <input type="hidden" id="tarifaBaseReserva" value="{{ $baseCategoria }}">
                                <div class="visor-total-item"><strong>Subtotal:</strong>
                                    ${{ number_format($subtotal, 2) }}</div>
                                <div class="visor-total-item"><strong>IVA:</strong> ${{ number_format($iva, 2) }}</div>
                                <div class="visor-total-item visor-total-main"><strong>Total:</strong>
                                    ${{ number_format($total, 2) }}</div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="text-center my-4 visor-confirm-wrap">
            <form method="POST" action="{{ route('visor.reenviarCorreo', $reservacion->id_reservacion) }}">
                @csrf
                <button type="submit" class="btn-confirmar-cambios" id="btnConfirmarCambios">
                    Confirmar cambios y reenviar correo
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL CATEGORÍA --}}
    @php
        $predeterminados = [
            'C' => ['pax' => 5, 'small' => 2, 'big' => 1],
            'D' => ['pax' => 5, 'small' => 2, 'big' => 1],
            'E' => ['pax' => 5, 'small' => 2, 'big' => 2],
            'F' => ['pax' => 5, 'small' => 2, 'big' => 2],
            'IC' => ['pax' => 5, 'small' => 2, 'big' => 2],
            'I' => ['pax' => 5, 'small' => 3, 'big' => 2],
            'IB' => ['pax' => 7, 'small' => 3, 'big' => 2],
            'M' => ['pax' => 7, 'small' => 4, 'big' => 2],
            'L' => ['pax' => 13, 'small' => 4, 'big' => 3],
            'H' => ['pax' => 5, 'small' => 3, 'big' => 2],
            'HI' => ['pax' => 5, 'small' => 3, 'big' => 2],
        ];

        if (!function_exists('img_por_categoria_modal')) {
            function img_por_categoria_modal($codigo)
            {
                switch ($codigo) {
                    case 'C':
                        return asset('img/aveo.png');
                    case 'D':
                        return asset('img/virtus.png');
                    case 'E':
                        return asset('img/jetta.png');
                    case 'F':
                        return asset('img/camry.png');
                    case 'IC':
                        return asset('img/renegade.png');
                    case 'I':
                        return asset('img/taos.png');
                    case 'IB':
                        return asset('img/avanza.png');
                    case 'M':
                        return asset('img/Odyssey.png');
                    case 'L':
                        return asset('img/Hiace.png');
                    case 'H':
                        return asset('img/Frontier.png');
                    case 'HI':
                        return asset('img/Tacoma.png');
                    default:
                        return asset('img/Logotipo.png');
                }
            }
        }

        $ordenCategoriasModal = ['C', 'D', 'E', 'F', 'IC', 'I', 'IB', 'H', 'HI', 'L', 'M'];

        $categoriasModal = collect($categoriasCards ?? [])->sortBy(function ($item) use ($ordenCategoriasModal) {
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
                    <div class="row g-3">
                        @forelse ($categoriasModal as $cat)
                            @php
                                $codigo = $cat->codigo;
                                $cap = $predeterminados[$codigo] ?? ['pax' => 5, 'small' => 2, 'big' => 1];
                                $img = img_por_categoria_modal($codigo);
                                $textoCategoria = $codigo . ' | ' . $cat->nombre;
                            @endphp

                            <div class="col-lg-4 col-md-6">
                                <article class="car-card catalog-group h-100">
                                    @if (!empty($cat->descuento_miembro) && $cat->descuento_miembro > 0)
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

                                    <button type="button" class="car-cta elegirCategoria"
                                        data-id="{{ $cat->id_categoria }}" data-texto="{{ $textoCategoria }}"
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

    <div class="vr-update-overlay" id="vrUpdateOverlay" aria-hidden="true">
        <div class="vr-update-card" role="status" aria-live="polite">

            <div id="vrUpdateLoader">
                <div class="vr-emoji-stage">
                    <span class="vr-icon-car">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 17H3v-5l2-5h11l3 5h1a1 1 0 0 1 1 1v4h-2" />
                            <path d="M9 17h6" />
                            <circle cx="7" cy="17" r="2" />
                            <circle cx="17" cy="17" r="2" />
                            <path d="M5 12h14" />
                        </svg>
                    </span>
                    <span class="vr-emoji-road"></span>
                </div>
                <p class="vr-update-msg" id="vrUpdateMsg">Procesando tu información…</p>
            </div>

            <div id="vrUpdateSuccess">
                <div class="vr-icon-thumb">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M7 10v12" />
                        <path
                            d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" />
                    </svg>
                </div>
                <h4 class="vr-update-title">¡Información actualizada!</h4>
                <p class="vr-update-sub">Los cambios se guardaron correctamente</p>
            </div>

        </div>
    </div>
@endsection

@section('js-visorReservacion')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="{{ asset('js/visorReservacion.js') }}" defer></script>
@endsection
