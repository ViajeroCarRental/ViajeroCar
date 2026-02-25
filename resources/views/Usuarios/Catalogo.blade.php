@extends('layouts.Usuarios')

@section('Titulo','Catálogo de Vehículos')

@section('css-VistaCatalogo')
  <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endsection

@section('contenidoCatalogo')

@php
    $mapaCategorias = [
        'C' => 'Sedan', 'D' => 'Sedan', 'E' => 'Sedan', 'F' => 'Sedan',
        'IC' => 'SUV', 'I' => 'SUV', 'IB' => 'SUV',
        'H' => 'Pickup', 'HI' => 'Pickup',
        'L' => 'Van', 'M' => 'Van'
    ];

    $conteoTipos = ['Sedan' => 0, 'SUV' => 0, 'Pickup' => 0, 'Van' => 0];

    foreach($categoriasCards as $cat) {
        $tipoRelativo = $mapaCategorias[$cat->codigo] ?? null;
        if($tipoRelativo && isset($conteoTipos[$tipoRelativo])) {
            $conteoTipos[$tipoRelativo]++;
        }
    }

    $predeterminados = [
      'C'  => ['pax'=>5,  'small'=>2, 'big'=>1], 'D'  => ['pax'=>5,  'small'=>2, 'big'=>1],
      'E'  => ['pax'=>5,  'small'=>2, 'big'=>2], 'F'  => ['pax'=>5,  'small'=>2, 'big'=>2],
      'IC' => ['pax'=>5,  'small'=>2, 'big'=>2], 'I'  => ['pax'=>5,  'small'=>3, 'big'=>2],
      'IB' => ['pax'=>7,  'small'=>3, 'big'=>2], 'M'  => ['pax'=>7,  'small'=>4, 'big'=>2],
      'L'  => ['pax'=>13, 'small'=>4, 'big'=>3], 'H'  => ['pax'=>5,  'small'=>3, 'big'=>2],
      'HI' => ['pax'=>5,  'small'=>3, 'big'=>2],
    ];

    if (!function_exists('img_por_categoria')) {
        function img_por_categoria($codigo) {
            switch ($codigo) {
                case 'C':  return asset('img/aveo.png');
                case 'D':  return asset('img/virtus.png');
                case 'E':  return asset('img/jetta.png');
                case 'F':  return asset('img/camry.png');
                case 'IC': return asset('img/renegade.png');
                case 'I':  return asset('img/seltos.png');
                case 'IB': return asset('img/avanza.png');
                case 'M':  return asset('img/Odyssey.png');
                case 'L':  return asset('img/Hiace.png');
                case 'H':  return asset('img/Frontier.png');
                case 'HI': return asset('img/Tacoma.png');
                default:   return asset('img/Logotipo.png');
            }
        }
    }

    $ordenCategorias = ['C','D','E','F','IC','I','IB','H','HI','L','M'];
    $categoriasCards = collect($categoriasCards)->sortBy(function($item) use ($ordenCategorias) {
        return array_search($item->codigo, $ordenCategorias);
    });

    $hayAutos = count($categoriasCards) > 0;
@endphp

  {{-- ========== HERO ========== --}}
  <section class="hero">
    <div class="hero-bg">
      <img src="{{ asset('img/catalogo.png') }}" alt="Catálogo ViajeroCar">
      <div class="overlay"></div>
    </div>

    <div class="hero-inner">
      <h1 class="hero-title">¡RENTA HOY, EXPLORA MAÑANA, VIAJA SIEMPRE!</h1>

      <div class="chips">
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Oficina Central</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Central Autobuses</span>
      </div>

      {{-- ✅ Bloque/card dentro del hero --}}
      <div class="hero-filter-card">
        <div class="filter-accordion">
          <button
            class="accordion-button bg-gray rounded-0 text-dark collapsed"
            id="btn-filtro-autos"
            type="button"
            aria-expanded="false"
            aria-controls="filtro-autos"
            aria-label="Despliega el filtro de categorías de autos"
          >
            <span class="acc-left">
              <i class="fa-solid fa-filter"></i>
              <span>Filtrar categorías</span>
            </span>

            <i class="fa-solid fa-chevron-down acc-icon"></i>
          </button>

          <div id="filtro-autos" class="accordion-collapse" aria-labelledby="btn-filtro-autos">
            <div class="accordion-body">
              <div class="filter-wrapper">
                <h3 class="filter-title">Tipos de autos:</h3>

                <div class="car-filter">
                  <div class="filter-card active" data-filter="all">
                    <img src="{{ asset('img/aveo.png') }}">
                    <div class="filter-info">
                      <span>Todos</span>
                      <small class="count-badge">{{ count($categoriasCards) }} unidades</small>
                    </div>
                  </div>

                  <div class="filter-card" data-filter="Sedan">
                    <img src="{{ asset('img/camry.png') }}">
                    <div class="filter-info">
                      <span>Sedan</span>
                      <small class="count-badge">{{ $conteoTipos['Sedan'] }} unidades</small>
                    </div>
                  </div>

                  <div class="filter-card" data-filter="SUV">
                    <img src="{{ asset('img/seltos.png') }}">
                    <div class="filter-info">
                      <span>SUV</span>
                      <small class="count-badge">{{ $conteoTipos['SUV'] }} unidades</small>
                    </div>
                  </div>

                  <div class="filter-card" data-filter="Pickup">
                    <img src="{{ asset('img/Frontier.png') }}">
                    <div class="filter-info">
                      <span>Pick up</span>
                      <small class="count-badge">{{ $conteoTipos['Pickup'] }} unidades</small>
                    </div>
                  </div>

                  <div class="filter-card" data-filter="Van">
                    <img src="{{ asset('img/Hiace.png') }}">
                    <div class="filter-info">
                      <span>Van</span>
                      <small class="count-badge">{{ $conteoTipos['Van'] }} unidades</small>
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

  {{-- ========== CATÁLOGO (2 columnas, estilo como tu referencia) ========== --}}
  <section class="catalog">
    <div class="cars cars-grid">
      @foreach ($categoriasCards as $cat)
        @php
          $codigo = $cat->codigo;
          $tipo   = $mapaCategorias[$codigo] ?? 'Sedan';
          $cap    = $predeterminados[$codigo] ?? ['pax'=>5,'small'=>2,'big'=>1];
          $img    = img_por_categoria($codigo);

          $precio = (float)($cat->precio_dia ?? 0);
          $desc   = (float)($cat->descuento_miembro ?? 0); // % (0-100)
          $hayDesc = $desc > 0;

          // precio tachado (antes del descuento)
          $precioOld = ($hayDesc && $desc < 100) ? ($precio / (1 - ($desc/100))) : 0;
        @endphp

        <article class="car-card catalog-group" data-categoria="{{ $tipo }}">

          {{-- ✅ badge descuento arriba derecha --}}
          @if($hayDesc)
            <span class="offer-badge">-{{ (int)round($desc) }}%</span>
          @endif

          {{-- ✅ TEXTO con el estilo que ya traíamos (h3 + p) --}}
          <header class="car-title">
            <h3>{{ strtoupper($cat->nombre) }}</h3>
            <p>{{ $cat->descripcion }} | {{ $codigo }}</p>
          </header>

          <div class="car-media">
            <img src="{{ $img }}" alt="{{ $cat->nombre }}">
          </div>

          {{-- ✅ ICONOS centrados --}}
          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> {{ $cap['pax'] }}</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }}</li>
            <li><i class="fa-solid fa-briefcase"></i> {{ $cap['big'] ?? 1 }}</li>
          </ul>

          {{-- ✅ Android Auto / CarPlay: icono como tu imagen + texto con chip --}}
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

          <a href="{{ route('rutaReservasIniciar', ['categoria_id' => $cat->id_categoria]) }}" class="car-cta">
            Reservar
          </a>
        </article>
      @endforeach

      @if(!$hayAutos)
        <div class="no-results">
          <h3>Sin vehículos disponibles</h3>
        </div>
      @endif
    </div>
  </section>

@endsection

@section('js-vistaCatalogo')
  <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection  