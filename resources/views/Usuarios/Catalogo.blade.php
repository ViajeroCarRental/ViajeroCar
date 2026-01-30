@extends('layouts.Usuarios')

@section('Titulo','Catálogo de Vehículos')

@section('css-VistaCatalogo')
  <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endsection

@section('contenidoCatalogo')

  {{-- ========== HERO ========== --}}
  <section class="hero">
    <div class="hero-bg">
      <img src="{{ asset('img/catalogo.png') }}" alt="Catálogo ViajeroCar">
    </div>
    <div class="overlay"></div>

    <div class="hero-inner">
      <h1 class="hero-title">¡RENTA HOY, EXPLORA MAÑANA, VIAJA SIEMPRE!</h1>
      <div class="chips">
        <span class="chip">
          <i class="fa-solid fa-location-dot"></i>
          Pick-up Oficina Central Plaza Park, Querétaro
        </span>
        <span class="chip">
          <i class="fa-solid fa-location-dot"></i>
          Pick-up Aeropuerto de Querétaro
        </span>
        <span class="chip">
          <i class="fa-solid fa-location-dot"></i>
          Pick-up Central de Autobuses de Querétaro
        </span>
      </div>
    </div>
  </section>

  @php
    // Capacidad predeterminada por código de categoría
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

    // Imagen por categoría
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
  @endphp

  {{-- ========== CATÁLOGO ========== --}}
  <section class="catalog">
    <div class="cars">

      @php $hayAutos = count($categoriasCards) > 0; @endphp

      @foreach ($categoriasCards as $cat)
        @php
          $codigo  = $cat->codigo;
          $titulo  = $cat->nombre;
          $desc    = $cat->descripcion;
          $precio  = $cat->precio_dia;

          $cap = $predeterminados[$codigo] ?? [
            'pax'   => 5,
            'small' => 2,
            'big'   => 1,
          ];

          $img = img_por_categoria($codigo);
        @endphp

        <div class="catalog-group">
          <div class="catalog-group-head">
            <h2 class="catalog-group-title">
              {{ $codigo }} · {{ $titulo }}
            </h2>
            <p class="catalog-group-subtitle">
              {{ $desc }}
            </p>
          </div>

          <div class="car-list">
            <article class="car car-long">

              {{-- Imagen --}}
              <div class="car-media">
                <img src="{{ $img }}" alt="{{ $titulo }}">
              </div>

              {{-- Info --}}
              <div class="car-main">
                <span class="car-pill">
                  <i class="fa-solid fa-car-side"></i>
                  {{ $codigo }} · {{ $titulo }}
                </span>

                <h3 class="car-name">
                  <span class="car-brand">Categoría</span>
                  <span class="car-model">{{ $titulo }}</span>
                </h3>

                <p class="subtitle">
                  Transmisión manual o automática
                </p>

                <ul class="car-specs">
                  <li>
                    <i class="fa-solid fa-user-group"></i>
                    {{ $cap['pax'] }} pasajeros
                  </li>
                  <li>
                    <i class="fa-solid fa-suitcase-rolling"></i>
                    {{ $cap['small'] }} maletas chicas
                  </li>
                  <li>
                    <i class="fa-solid fa-suitcase"></i>
                    {{ $cap['big'] }} maletas grandes
                  </li>
                </ul>

                <div class="car-tech">
                  <span class="car-tech-badge yes">
                    <i class="fa-brands fa-apple"></i> Apple CarPlay
                  </span>
                  <span class="car-tech-badge yes">
                    <i class="fa-brands fa-android"></i> Android Auto
                  </span>
                </div>

                <p class="incluye">
                  {{ $desc }}. Tarifas sujetas a disponibilidad y temporada.
                </p>
              </div>

              {{-- Precio / CTA --}}
              <div class="car-cta">
                <div class="price">
                  <span class="from">DESDE</span>
                  <div class="amount">
                    ${{ number_format((float)$precio, 0) }} <small>MXN</small>
                  </div>
                  <span class="per">por día</span>
                </div>

                <a
                  href="{{ route('rutaReservasIniciar', ['categoria_id' => $cat->id_categoria]) }}"
                  class="btn btn-primary"
                >
                  <i class="fa-regular fa-calendar-check"></i>
                  ¡Reserva ahora!
                </a>
              </div>

            </article>
          </div>
        </div>
      @endforeach

      @if(!$hayAutos)
        <div class="no-results" style="grid-column:1/-1; text-align:center; padding:2rem 1rem;">
          <h3>Sin vehículos disponibles</h3>
          <p>Vuelve más tarde para conocer nuestras opciones.</p>
        </div>
      @endif

    </div>
  </section>

@endsection

@section('js-vistaCatalogo')
  <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection
