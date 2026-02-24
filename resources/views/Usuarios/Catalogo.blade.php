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
    </div>
    <div class="overlay"></div>

    <div class="hero-inner">
      <h1 class="hero-title">¡RENTA HOY, EXPLORA MAÑANA, VIAJA SIEMPRE!</h1>

      <div class="chips">
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Oficina Central</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Central Autobuses</span>
      </div>

      {{-- FILTROS DENTRO DEL HERO  --}}
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
      </div> {{-- Fin filter-wrapper --}}
    </div> {{-- Fin hero-inner --}}
  </section> {{-- Fin hero --}}

  {{-- ========== CATÁLOGO ========== --}}
  <section class="catalog">
    <div class="cars">
      @foreach ($categoriasCards as $cat)
        @php
          $codigo  = $cat->codigo;
          $cap = $predeterminados[$codigo] ?? ['pax'=>5,'small'=>2,'big'=>1];
          $img = img_por_categoria($codigo);
          $tipo = $mapaCategorias[$codigo] ?? 'Sedan';
        @endphp

        <div class="catalog-group" data-categoria="{{ $tipo }}">
          <div class="catalog-group-head">
            <h2 class="catalog-group-title">{{ $codigo }} · {{ $cat->nombre }}</h2>
            <p class="catalog-group-subtitle">{{ $cat->descripcion }}</p>
          </div>

          <div class="car-list">
            <article class="car car-long">
              <div class="car-media">
                <img src="{{ $img }}" alt="{{ $cat->nombre }}">
              </div>

              <div class="car-main">
                <span class="car-pill"><i class="fa-solid fa-car-side"></i> {{ $codigo }} · {{ $cat->nombre }}</span>
                <h3 class="car-name">
                  <span class="car-brand">Categoría</span>
                  <span class="car-model">{{ $cat->nombre }}</span>
                </h3>
                <ul class="car-specs">
                  <li><i class="fa-solid fa-user-group"></i> {{ $cap['pax'] }} pasajeros</li>
                  <li><i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }} maletas</li>
                </ul>
                <p class="incluye">{{ $cat->descripcion }}</p>
              </div>

              <div class="car-cta">
                <div class="price">
                  <span class="from">DESDE</span>
                  <div class="amount">${{ number_format((float)$cat->precio_dia, 0) }} <small>MXN</small></div>
                  <span class="per">por día</span>
                </div>
                <a href="{{ route('rutaReservasIniciar', ['categoria_id' => $cat->id_categoria]) }}" class="btn btn-primary">
                  <i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!
                </a>
              </div>
            </article>
          </div>
        </div>
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
