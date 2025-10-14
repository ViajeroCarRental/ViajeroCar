@extends('layouts.Usuarios')

@section('Titulo','Catálogo de Vehículos')

@section('css-VistaCatalogo')
  <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endsection

@section('contenidoCatalogo')

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg">
      <img src="{{ asset('img/catalogo.png') }}" alt="FAQ ViajeroCar">
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

  <!-- FILTROS -->
  <section class="filters" aria-labelledby="filtros-title">
    <h2 id="filtros-title" class="sr-only" style="position:absolute;left:-9999px">Filtros del catálogo</h2>

    <form class="filter-row" action="{{ route('rutaCatalogoResultados') }}" method="GET">
      <div class="field">
        <label for="f-location">Ubicación</label>
        <select id="f-location" name="location">
          <option value="">Todas</option>
          @foreach ($ciudades as $c)
            <option value="{{ $c->id_sucursal }}" {{ (string)request('location')===(string)$c->id_sucursal ? 'selected' : '' }}>
              {{ $c->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label for="f-type">Tipo</label>
        <select id="f-type" name="type">
          <option value="">Todos</option>
          @foreach ($categorias as $cat)
            <option value="{{ $cat->id_categoria }}" {{ (string)request('type')===(string)$cat->id_categoria ? 'selected' : '' }}>
              {{ $cat->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Entrega</label>
        <div class="nice-date" data-bind="start">
          <i class="fa-regular fa-calendar"></i>
          <input id="date-start" name="start" type="text" placeholder="dd/mm/aaaa"
                 value="{{ request('start') }}" readonly>
          <div class="cal-pop" aria-hidden="true"></div>
        </div>
      </div>

      <div class="field">
        <label>Devolución</label>
        <div class="nice-date" data-bind="end">
          <i class="fa-regular fa-calendar"></i>
          <input id="date-end" name="end" type="text" placeholder="dd/mm/aaaa"
                 value="{{ request('end') }}" readonly>
          <div class="cal-pop" aria-hidden="true"></div>
        </div>
      </div>

      <div class="field actions">
        <button class="btn btn-primary" type="submit">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
      </div>
    </form>

    @isset($mensaje)
      <div class="filter-hint" style="margin-top:.75rem">
        <small>{{ $mensaje }}</small>
      </div>
    @endisset
  </section>

  <!-- CATÁLOGO (DINÁMICO) -->
  <section class="catalog">
    <div class="cars">
      @forelse ($autos as $auto)
        @php
          $trans = strtoupper(substr((string)$auto->transmision, 0, 1)) ?: 'A';
          $img   = $auto->img_url
                    ? (\Illuminate\Support\Str::startsWith($auto->img_url, ['http://','https://'])
                        ? $auto->img_url
                        : asset($auto->img_url))
                    : asset('img/placeholder-car.jpg');
        @endphp

        <article class="car"
                 data-type="{{ \Illuminate\Support\Str::slug($auto->categoria) }}"
                 data-trans="{{ $trans === 'M' ? 'manual' : 'automatico' }}"
                 data-location="{{ \Illuminate\Support\Str::slug($auto->sucursal ?? 'general') }}">
          <div class="car-media">
            <img src="{{ $img }}" alt="{{ $auto->nombre_publico }}">
          </div>

          <div class="car-body">
            <h3>
              {{ $auto->marca }}
              <strong>{{ $auto->modelo }}</strong>
              <small style="font-weight:normal">({{ $auto->anio }})</small>
            </h3>

            <div class="subtitle">
              {{ strtoupper($auto->categoria) }}
              @if(!empty($auto->sucursal))
                | <span class="cat">{{ $auto->sucursal }}</span>
              @endif
            </div>

            <ul class="features">
              <li title="Pasajeros"><i class="fa-solid fa-user-group"></i> {{ (int)$auto->asientos }}</li>
              <li title="Puertas"><i class="fa-solid fa-door-open"></i> {{ (int)$auto->puertas }}</li>
              <li title="Transmisión"><i class="fa-solid fa-gear"></i> {{ $trans }}</li>
            </ul>

            @if(!empty($auto->descripcion))
              <p class="incluye">{{ $auto->descripcion }}</p>
            @endif
          </div>

          <div class="car-cta">
            <div class="price">
              <span class="from">DESDE</span>
              <div class="amount">
                ${{ number_format((float)$auto->precio_dia, 0) }} <small>MXN</small>
              </div>
              <span class="per">por día</span>
            </div>

            {{-- >>> Enlace que pasa filtros y fechas al flujo de reservaciones <<< --}}
            <a
              href="{{ route('reservaciones.desdeCatalogo', [
                  'pickup_sucursal_id'  => request('location'),           // mismo lugar para dropoff por defecto
                  'dropoff_sucursal_id' => request('location'),
                  'pickup_date'         => request('start'),              // dd/mm/aaaa o yyyy-mm-dd (se normaliza)
                  'pickup_time'         => '12:00 pm',                    // default
                  'dropoff_date'        => request('end'),
                  'dropoff_time'        => '11:00 am',                    // default
                  'categoria_id'        => request('type'),               // categoría filtrada
                  'vehiculo_id'         => $auto->id_vehiculo ?? null,    // por si quieres saltar directo
              ]) }}"
              class="btn btn-primary"
            >
              <i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!
            </a>
          </div>
        </article>
      @empty
        <div class="no-results" style="grid-column:1/-1; text-align:center; padding:2rem 1rem;">
          <h3>Sin resultados 🕵️‍♂️</h3>
          <p>Intenta cambiar la ubicación, el tipo o el rango de fechas.</p>
        </div>
      @endforelse
    </div>
  </section>

@endsection

{{-- === JS de esta vista === --}}
@section('js-vistaCatalogo')
  <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection
