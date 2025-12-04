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
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Oficina Central Park, Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto de Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto Intl del Bajío, León</span>
      </div>
    </div>
  </section>

  @php
    use Illuminate\Support\Str;

    // === Normalizador de texto (para hacer coincidir nombres aunque cambie mayúsculas/plural/etc.) ===
    $normalize = function ($s) {
        return Str::of($s ?? '')
            ->lower()
            ->replace(['–','-'], ' ')
            ->squish()
            ->__toString();
    };

    // 🔹 Config oficial de categorías (cómo quieres mostrarlas)
    // "db" = cómo las tienes más o menos en la BD (texto aproximado)
    $configCategorias = [
      [
        'db'      => 'Compacto',
        'codigo'  => 'C',
        'titulo'  => 'Compacto',
        'ejemplo' => 'Chevrolet Aveo o similar',
      ],
      [
        'db'      => 'Medianos',
        'codigo'  => 'D',
        'titulo'  => 'Mediano',
        'ejemplo' => 'Nissan Versa o similar',
      ],
      [
        'db'      => 'Grandes',
        'codigo'  => 'E',
        'titulo'  => 'Grande',
        'ejemplo' => 'Volkswagen Jetta o similar',
      ],
      [
        'db'      => 'Full size',
        'codigo'  => 'F',
        'titulo'  => 'Full Size',
        'ejemplo' => 'Toyota Camry o similar',
      ],
      [
        'db'      => 'Suv compacta',
        'codigo'  => 'IC',
        'titulo'  => 'SUV Compacta',
        'ejemplo' => 'Jeep Renegade o similar',
      ],
      [
        'db'      => 'Suv Mediana',
        'codigo'  => 'I',
        'titulo'  => 'SUV Mediana',
        'ejemplo' => 'Kia Seltos o similar',
      ],
      [
        'db'      => 'Suv Familiar compacta',
        'codigo'  => 'IB',
        'titulo'  => 'SUV Familiar compacta',
        'ejemplo' => 'Toyota Avanza o similar',
      ],
      [
        'db'      => 'Minivan',
        'codigo'  => 'M',
        'titulo'  => 'Minivan',
        'ejemplo' => 'Honda Odyssey o similar',
      ],
      [
        'db'      => 'Hasta 13- usuarios',
        'codigo'  => 'L',
        'titulo'  => 'Hasta 13 pasajeros',
        'ejemplo' => 'Toyota Hiace o similar',
      ],
      [
        'db'      => 'Pick up Doble Cabina',
        'codigo'  => 'H',
        'titulo'  => 'Pick Up doble cabina',
        'ejemplo' => 'Nissan Frontier o similar',
      ],
      [
        'db'      => 'Pick up 4x4 Doble Cabina',
        'codigo'  => 'HI',
        'titulo'  => 'Pick Up 4x4 doble cabina',
        'ejemplo' => 'Toyota Tacoma o similar',
      ],
    ];

    // 🔹 Capacidad predeterminada por código
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

    // 🔹 Indexar las categorías de BD por nombre normalizado
    $categoriasIndex = [];
    foreach ($categorias as $cat) {
        $categoriasIndex[$normalize($cat->nombre)] = $cat;
    }

    // 🔹 Agrupar autos por nombre de categoría normalizado
    $autosPorCatNorm = [];
    foreach ($autos as $auto) {
        $norm = $normalize($auto->categoria ?? null);
        if (!$norm) continue;
        $autosPorCatNorm[$norm][] = $auto;
    }
  @endphp

  {{-- ========== FILTRO (CATEGORÍA) ========== --}}
  <section class="filters" aria-labelledby="filtros-title">
    <h2 id="filtros-title" class="sr-only">Filtros del catálogo</h2>

    <form class="filter-row" action="{{ route('rutaCatalogoResultados') }}" method="GET">
      <div class="field">
        <label for="f-type">Categoría</label>
        <select id="f-type" name="type">
          <option value="">Todas</option>

          @foreach ($configCategorias as $cfg)
            @php
              $normCfg = $normalize($cfg['db']);
              $catBd   = $categoriasIndex[$normCfg] ?? null;
            @endphp

            @if($catBd)
              @php
                $value    = $catBd->id_categoria;
                $selected = (string)request('type') === (string)$value ? 'selected' : '';
              @endphp
              <option value="{{ $value }}" {{ $selected }}>
                {{ $cfg['codigo'] }} · {{ $cfg['titulo'] }} — {{ $cfg['ejemplo'] }}
              </option>
            @endif
          @endforeach
        </select>
      </div>

      <div class="field actions">
        <button class="btn btn-primary" type="submit">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
      </div>
    </form>
  </section>

  {{-- ========== CATÁLOGO AGRUPADO POR CATEGORÍA ========== --}}
  <section class="catalog">
    <div class="cars">
      @php $hayAutos = false; @endphp

      @foreach ($configCategorias as $cfg)
        @php
          $normCfg = $normalize($cfg['db']);
          $lista   = $autosPorCatNorm[$normCfg] ?? [];
          $lista   = array_slice($lista, 0, 3); // máximo 3 autos por categoría
        @endphp

        @if (!empty($lista))
          @php $hayAutos = true; @endphp

          <div class="catalog-group">
            <div class="catalog-group-head">
              <h2 class="catalog-group-title">
                {{ $cfg['codigo'] }} · {{ $cfg['titulo'] }}
              </h2>
              <p class="catalog-group-subtitle">
                {{ $cfg['ejemplo'] }}
              </p>
            </div>

            <div class="car-list">
              @foreach ($lista as $auto)
                @php
                  // Imagen
                  $img = $auto->img_url
                        ? (Str::startsWith($auto->img_url, ['http://','https://'])
                            ? $auto->img_url
                            : asset($auto->img_url))
                        : asset('img/placeholder-car.jpg');

                  // Capacidad según código
                  $cap = $predeterminados[$cfg['codigo']] ?? [
                    'pax'   => (int)($auto->asientos ?? 5),
                    'small' => 2,
                    'big'   => 1,
                  ];

                  // Transmisión texto
                  $trans = strtoupper(substr((string)$auto->transmision, 0, 1)) === 'M'
                           ? 'Manual'
                           : 'Automática';
                @endphp

                <article class="car car-long">
                  {{-- IMAGEN --}}
                  <div class="car-media">
                    <img src="{{ $img }}" alt="{{ $auto->nombre_publico ?? ($auto->marca.' '.$auto->modelo) }}">
                  </div>

                  {{-- INFO CENTRAL --}}
                  <div class="car-main">
                    {{-- Pill de categoría --}}
                    <span class="car-pill">
                      <i class="fa-solid fa-car-side"></i>
                      {{ $cfg['codigo'] }} · {{ $cfg['titulo'] }}
                    </span>

                    <h3 class="car-name">
                      <span class="car-brand">{{ $auto->marca }}</span>
                      <span class="car-model">{{ $auto->modelo }}</span>
                    </h3>

                    <p class="subtitle">
                      Transmisión {{ $trans }}
                    </p>

                    <ul class="car-specs">
                      <li title="Personas">
                        <i class="fa-solid fa-user-group"></i> {{ $cap['pax'] }} pasajeros
                      </li>
                      <li title="Maletas chicas">
                        <i class="fa-solid fa-suitcase-rolling"></i> {{ $cap['small'] }} maletas chicas
                      </li>
                      <li title="Maletas grandes">
                        <i class="fa-solid fa-suitcase"></i> {{ $cap['big'] }} maletas grandes
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

                    @if(!empty($auto->descripcion))
                      <p class="incluye">{{ $auto->descripcion }}</p>
                    @endif
                  </div>

                  {{-- PRECIO + CTA --}}
                  <div class="car-cta">
                    <div class="price">
                      <span class="from">DESDE</span>
                      <div class="amount">
                        ${{ number_format((float)$auto->precio_dia, 0) }} <small>MXN</small>
                      </div>
                      <span class="per">por día</span>
                    </div>

                    {{-- Botón: tu controller redirige a reservaciones.iniciar cuando recibe vehiculo_id --}}
                    <a
                      href="{{ route('rutaCatalogoResultados', [
                          'vehiculo_id' => $auto->id_vehiculo,
                      ]) }}"
                      class="btn btn-primary"
                    >
                      <i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!
                    </a>
                  </div>
                </article>
              @endforeach
            </div>
          </div>
        @endif
      @endforeach

      @if(!$hayAutos)
        <div class="no-results" style="grid-column:1/-1; text-align:center; padding:2rem 1rem;">
          <h3>Sin vehículos disponibles</h3>
          <p>Intenta cambiar la categoría o vuelve más tarde.</p>
        </div>
      @endif

    </div>
  </section>

@endsection

@section('js-vistaCatalogo')
  <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection
