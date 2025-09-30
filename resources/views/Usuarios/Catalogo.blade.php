@extends('layouts.Usuarios')

@section('Titulo','Catálogo de Vehículos')

@section('css-VistaCatalogo')

    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endsection

@section('contenidoCatalogo')

     <section class="hero">
    <div class="hero-bg" style="background-image:url('https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=1600&auto=format&fit=crop');"></div>
    <div class="overlay"></div>

    <div class="hero-inner">
      <h1 class="hero-title">¡RENTA HOY, EXPLORA MAÑANA, VIAJA SIEMPRE!</h1>
      <div class="chips">
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Oficina Central Park, Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto de Querétaro</span>
        <span class="chip"><i class="fa-solid fa-location-dot"></i> Pick-up Aeropuerto de León</span>
      </div>
    </div>
  </section>

  <section class="filters" aria-labelledby="filtros-title">
    <h2 id="filtros-title" class="sr-only" style="position:absolute;left:-9999px">Filtros del catálogo</h2>
    <form class="filter-row" onsubmit="event.preventDefault();">
      <div class="field">
        <label for="f-location">Ubicación</label>
        <select id="f-location">
          <option value="all">Todas</option>
          <option value="central">Querétaro (Central Park)</option>
          <option value="aiq">Aeropuerto de Querétaro</option>
          <option value="bjx">Aeropuerto de León</option>
        </select>
      </div>

      <div class="field">
        <label for="f-type">Tipo</label>
        <select id="f-type">
          <option value="all">Todos</option>
          <option value="compacto">Compacto</option>
          <option value="intermedio">Intermedio</option>
          <option value="suv">SUV</option>
          <option value="lujo">Lujo</option>
        </select>
      </div>

      <div class="field">
        <label>Entrega</label>
        <div class="nice-date" data-bind="start">
          <i class="fa-regular fa-calendar"></i>
          <input id="date-start" type="text" placeholder="dd/mm/aaaa" readonly>
          <div class="cal-pop" aria-hidden="true"></div>
        </div>
      </div>

      <div class="field">
        <label>Devolución</label>
        <div class="nice-date" data-bind="end">
          <i class="fa-regular fa-calendar"></i>
          <input id="date-end" type="text" placeholder="dd/mm/aaaa" readonly>
          <div class="cal-pop" aria-hidden="true"></div>
        </div>
      </div>

      <div class="field actions">
        <button class="btn btn-primary" id="btn-filter" type="button"><i class="fa-solid fa-filter"></i> Filtrar</button>
      </div>
    </form>
  </section>

  <section class="catalog">
    <div class="cars">
      <article class="car" data-type="compacto" data-trans="manual" data-location="central">
        <div class="car-media">
          <img src="https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop" alt="Chevrolet Aveo">
        </div>
        <div class="car-body">
          <h3>Chevrolet <strong>Aveo</strong> o similar</h3>
          <div class="subtitle">COMPACTO | <span class="cat">Categoría C</span></div>
          <ul class="features">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-door-open"></i> 4</li>
            <li><i class="fa-solid fa-gear"></i> M</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incluye">KM ilimitados · Relevo de Responsabilidad (LI)</p>
        </div>
        <div class="car-cta">
          <div class="price">
            <span class="from">DESDE</span>
            <div class="amount">$499 <small>MXN</small></div>
            <span class="per">por día</span>
          </div>
          <a href="reserva.html" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!</a>
        </div>
      </article>

      <article class="car" data-type="intermedio" data-trans="automatico" data-location="aiq">
        <div class="car-media">
          <img src="https://images.unsplash.com/photo-1606661421950-0f23d4f9f4ce?q=80&w=1200&auto=format&fit=crop" alt="Volkswagen Virtus">
        </div>
        <div class="car-body">
          <h3>Volkswagen <strong>Virtus</strong> o similar</h3>
          <div class="subtitle">INTERMEDIO | <span class="cat">Categoría D</span></div>
          <ul class="features">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
            <li><i class="fa-solid fa-door-open"></i> 4</li>
            <li><i class="fa-solid fa-gear"></i> A</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incluye">Cobertura básica incluida · Asistencia 24/7</p>
        </div>
        <div class="car-cta">
          <div class="price">
            <span class="from">DESDE</span>
            <div class="amount">$699 <small>MXN</small></div>
            <span class="per">por día</span>
          </div>
          <a href="reserva.html" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!</a>
        </div>
      </article>

      <article class="car" data-type="suv" data-trans="automatico" data-location="bjx">
        <div class="car-media">
          <img src="https://images.unsplash.com/photo-1603380355075-45a9cd9bba56?q=80&w=1200&auto=format&fit=crop" alt="Kia Sportage">
        </div>
        <div class="car-body">
          <h3>Kia <strong>Sportage</strong> o similar</h3>
          <div class="subtitle">SUV | <span class="cat">Categoría F</span></div>
          <ul class="features">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 4</li>
            <li><i class="fa-solid fa-door-open"></i> 5</li>
            <li><i class="fa-solid fa-gear"></i> A</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incluye">Espacio y confort para viajes largos</p>
        </div>
        <div class="car-cta">
          <div class="price">
            <span class="from">DESDE</span>
            <div class="amount">$999 <small>MXN</small></div>
            <span class="per">por día</span>
          </div>
          <a href="reserva.html" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!</a>
        </div>
      </article>

      <article class="car" data-type="lujo" data-trans="automatico" data-location="central">
        <div class="car-media">
          <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?q=80&w=1200&auto=format&fit=crop" alt="BMW Serie 3">
        </div>
        <div class="car-body">
          <h3>BMW <strong>Serie 3</strong> o similar</h3>
          <div class="subtitle">LUJO | <span class="cat">Categoría L</span></div>
          <ul class="features">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
            <li><i class="fa-solid fa-door-open"></i> 4</li>
            <li><i class="fa-solid fa-gear"></i> A</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incluye">Lujo y desempeño con máxima seguridad</p>
        </div>
        <div class="car-cta">
          <div class="price">
            <span class="from">DESDE</span>
            <div class="amount">$1,899 <small>MXN</small></div>
            <span class="per">por día</span>
          </div>
          <a href="reserva.html" class="btn btn-primary"><i class="fa-regular fa-calendar-check"></i> ¡Reserva ahora!</a>
        </div>
      </article>
    </div>
  </section>

@section('js-vistaHome')
    <script src="{{ asset('js/catalogo.js') }}"></script>
@endsection

@endsection
