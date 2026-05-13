@extends('layouts.Usuarios')

@section('Titulo', __('Vehicle Catalog'))

@section('css-VistaCatalogo')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endsection

@section('contenidoCatalogo')
    @php
        // ============================================================
        // CONFIGURACIÓN DE CATEGORÍAS (centralizada)
        // ============================================================

        // Claves de traducción (los textos se traducen con __())
        $nombresClave = [
            'C'  => 'Compact',
            'D'  => 'Intermediate',
            'E'  => 'Full Size',
            'F'  => 'Full Size',
            'IC' => 'Compact SUV',
            'I'  => 'Midsize SUV',
            'IB' => 'Compact Family SUV',
            'H'  => 'Double Cab Pickup',
            'HI' => '4x4 Double Cab Pickup',
            'L'  => 'Passenger Van',
            'M'  => 'Minivan',
        ];

        $descripcionesClave = [
            'C'  => 'Chevrolet Aveo or similar | C',
            'D'  => 'Volkswagen Virtus or similar | D',
            'E'  => 'Volkswagen Jetta or similar | E',
            'F'  => 'Toyota Camry or similar | F',
            'IC' => 'Jeep Renegade or similar | IC',
            'I'  => 'Volkswagen Taos or similar | I',
            'IB' => 'Toyota Avanza or similar | IB',
            'H'  => 'Nissan Frontier or similar | E',
            'HI' => 'Toyota Tacoma or similar | F',
            'L'  => 'Toyota Hiace or similar | L',
            'M'  => 'Honda Odyssey or similar | M',
        ];

        // Tipo de vehículo (para el filtro)
        $mapaCategorias = [
            'C'  => 'Sedan', 'D'  => 'Sedan', 'E'  => 'Sedan', 'F'  => 'Sedan',
            'IC' => 'SUV',   'I'  => 'SUV',   'IB' => 'SUV',
            'H'  => 'Pickup','HI' => 'Pickup',
            'L'  => 'Van',   'M'  => 'Van',
        ];

        // Capacidad por categoría
        $predeterminados = [
            'C'  => ['pax' => 5,  'small' => 2, 'big' => 1],
            'D'  => ['pax' => 5,  'small' => 2, 'big' => 1],
            'E'  => ['pax' => 5,  'small' => 2, 'big' => 2],
            'F'  => ['pax' => 5,  'small' => 2, 'big' => 2],
            'IC' => ['pax' => 5,  'small' => 2, 'big' => 2],
            'I'  => ['pax' => 5,  'small' => 3, 'big' => 2],
            'IB' => ['pax' => 7,  'small' => 3, 'big' => 2],
            'M'  => ['pax' => 7,  'small' => 4, 'big' => 2],
            'L'  => ['pax' => 13, 'small' => 4, 'big' => 3],
            'H'  => ['pax' => 5,  'small' => 3, 'big' => 2],
            'HI' => ['pax' => 5,  'small' => 3, 'big' => 2],
        ];

        // Mapa de imágenes (todas las de coches son .webp)
        $imagenesCategoria = [
            'C'  => 'aveo.webp',
            'D'  => 'virtus.webp',
            'E'  => 'jetta.webp',
            'F'  => 'camry.webp',
            'IC' => 'renegade.webp',
            'I'  => 'taos.webp',
            'IB' => 'avanza.webp',
            'M'  => 'Odyssey.webp',
            'L'  => 'Hiace.webp',
            'H'  => 'Frontier.webp',
            'HI' => 'Tacoma.webp',
        ];

        // Orden de presentación de las categorías
        $ordenCategorias = ['C', 'D', 'E', 'F', 'IC', 'I', 'IB', 'H', 'HI', 'L', 'M'];

        // ============================================================
        // PROCESADO (orden + conteos)
        // ============================================================
        $categoriasCards = collect($categoriasCards)->sortBy(function ($item) use ($ordenCategorias) {
            return array_search($item->codigo, $ordenCategorias);
        });

        $conteoTipos = ['Sedan' => 0, 'SUV' => 0, 'Pickup' => 0, 'Van' => 0];
        foreach ($categoriasCards as $cat) {
            $tipoRelativo = $mapaCategorias[$cat->codigo] ?? null;
            if ($tipoRelativo && isset($conteoTipos[$tipoRelativo])) {
                $conteoTipos[$tipoRelativo]++;
            }
        }

        $hayAutos = count($categoriasCards) > 0;
    @endphp

    {{-- ========== HERO ========== --}}
    <section class="hero">
        <div class="hero-bg">
            {{-- Hero LCP: alta prioridad para que cargue primero --}}
            <img src="{{ asset('img/catalogo.png') }}"
                 alt="Catálogo ViajeroCar"
                 width="1920" height="1080"
                 fetchpriority="high">
            <div class="overlay"></div>
        </div>

        <div class="hero-inner">
            <h1 class="hero-title">{{ __('RENT TODAY, EXPLORE TOMORROW, TRAVEL FOREVER!') }}</h1>

            {{-- Bloque/card dentro del hero --}}
            <div class="hero-filter-card">
                <div class="filter-accordion">
                    <button
                        class="accordion-button bg-gray rounded-0 text-dark collapsed"
                        id="btn-filtro-autos"
                        type="button"
                        aria-expanded="false"
                        aria-controls="filtro-autos"
                        aria-label="{{ __('Filter categories') }}"
                        data-text-open="{{ __('Hide categories') }}"
                        data-text-closed="{{ __('Filter categories') }}"
                    >
                        <span class="acc-left">
                            <i class="fa-solid fa-filter"></i>
                            <span>{{ __('Filter categories') }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down acc-icon"></i>
                    </button>

                    <div id="filtro-autos" class="accordion-collapse" aria-labelledby="btn-filtro-autos">
                        <div class="accordion-body">
                            <div class="filter-wrapper">
                                <h3 class="filter-title">{{ __('Vehicle types:') }}</h3>

                                <div class="car-filter">
                                    <div class="filter-card active" data-filter="all">
                                        <img src="{{ asset('img/aveo.webp') }}"
                                             alt="{{ __('All') }}"
                                             width="90" height="68"
                                             loading="lazy">
                                        <div class="filter-info">
                                            <span>{{ __('All') }}</span>
                                            <small class="count-badge">{{ count($categoriasCards) }} {{ __('categories') }}</small>
                                        </div>
                                    </div>

                                    <div class="filter-card" data-filter="Sedan">
                                        <img src="{{ asset('img/camry.webp') }}"
                                             alt="{{ __('Cars') }}"
                                             width="90" height="68"
                                             loading="lazy">
                                        <div class="filter-info">
                                            <span>{{ __('Cars') }}</span>
                                            <small class="count-badge">{{ $conteoTipos['Sedan'] }} {{ __('categories') }}</small>
                                        </div>
                                    </div>

                                    <div class="filter-card" data-filter="SUV">
                                        <img src="{{ asset('img/taos.webp') }}"
                                             alt="{{ __('SUVs') }}"
                                             width="90" height="68"
                                             loading="lazy">
                                        <div class="filter-info">
                                            <span>{{ __('SUVs') }}</span>
                                            <small class="count-badge">{{ $conteoTipos['SUV'] }} {{ __('categories') }}</small>
                                        </div>
                                    </div>

                                    <div class="filter-card" data-filter="Pickup">
                                        <img src="{{ asset('img/Frontier.webp') }}"
                                             alt="{{ __('Pickups') }}"
                                             width="90" height="68"
                                             loading="lazy">
                                        <div class="filter-info">
                                            <span>{{ __('Pickups') }}</span>
                                            <small class="count-badge">{{ $conteoTipos['Pickup'] }} {{ __('categories') }}</small>
                                        </div>
                                    </div>

                                    <div class="filter-card" data-filter="Van">
                                        <img src="{{ asset('img/Odyssey.webp') }}"
                                             alt="{{ __('Vans') }}"
                                             width="90" height="68"
                                             loading="lazy">
                                        <div class="filter-info">
                                            <span>{{ __('Vans') }}</span>
                                            <small class="count-badge">{{ $conteoTipos['Van'] }} {{ __('categories') }}</small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== CATÁLOGO (2 columnas) ========== --}}
    <section class="catalog">
        <div class="cars cars-grid">

            @foreach ($categoriasCards as $cat)
                @php
                    $codigo = $cat->codigo;
                    $tipo   = $mapaCategorias[$codigo] ?? 'Sedan';
                    $cap    = $predeterminados[$codigo] ?? ['pax' => 5, 'small' => 2, 'big' => 1];

                    // Imagen (con fallback al logotipo si no está mapeada)
                    $imgFile = $imagenesCategoria[$codigo] ?? 'Logotipo.png';
                    $img     = asset('img/' . $imgFile);

                    $precio  = (float) ($cat->precio_dia ?? 0);
                    $desc    = (float) ($cat->descuento_miembro ?? 0); // % (0-100)
                    $hayDesc = $desc > 0;
                @endphp

                <article class="car-card catalog-group" data-categoria="{{ $tipo }}">

                    {{-- Badge descuento arriba derecha --}}
                    @if ($hayDesc)
                        <span class="offer-badge">-{{ (int) round($desc) }}%</span>
                    @endif

                    {{-- Título --}}
                    <header class="car-title">
                        <h3>{{ strtoupper(__($nombresClave[$codigo] ?? $cat->nombre)) }}</h3>
                        <p>{{ __($descripcionesClave[$codigo] ?? $cat->descripcion) }}</p>
                    </header>

                    {{-- Imagen del coche --}}
                    <div class="car-media">
                        <img src="{{ $img }}"
                             alt="{{ $cat->nombre }}"
                             width="360" height="203"
                             loading="lazy">
                    </div>

                    {{-- Iconos / especificaciones --}}
                    <ul class="car-specs">
                        <li><i class="fa-solid fa-user-large"></i> {{ $cap['pax'] }}</li>
                        <li><i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }}</li>
                        <li><i class="fa-solid fa-briefcase"></i> {{ $cap['big'] ?? 1 }}</li>

                        <li title="{{ __('Transmission') }}">
                            <span class="spec-letter">
                                T | {{ $codigo === 'L' ? __('Manual') : __('Automatic') }}
                            </span>
                        </li>

                        <li title="{{ __('Air conditioning') }}">
                            <i class="fa-regular fa-snowflake"></i> A/C
                        </li>
                    </ul>

                    {{-- Android Auto / CarPlay --}}
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

                    <a href="{{ route('rutaReservasIniciar', ['categoria_id' => $cat->id_categoria]) }}"
                       class="car-cta">
                        {{ __('Book now') }}
                    </a>
                </article>
            @endforeach

            @if (!$hayAutos)
                <div class="no-results">
                    <h3>{{ __('No vehicles available') }}</h3>
                </div>
            @endif

        </div>
    </section>

@endsection

@section('js-vistaCatalogo')
    <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection
