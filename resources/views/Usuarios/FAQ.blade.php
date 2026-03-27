@extends('layouts.Usuarios')

@section('Titulo', __('FAQ | ViajeroCar'))

@section('css-vistaFAQ')
  <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
@endsection

@section('contenidoFAQ')
<main class="page">
  <!-- HERO -->
  <section class="hero hero-mini">
    <div class="hero-bg">
      <img src="{{ asset('img/faq.jpg') }}" alt="FAQ ViajeroCar">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content left">
      <div class="hero-text">
        <h1>{{ __('Help center') }}</h1>
        <p>{{ __('Resolve your questions by chatting with our assistant') }}</p>

      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="faq-wrap">
    <aside class="faq-side">
      <h3><i class="fa-solid fa-folder-open"></i> {{ __('Categories') }}</h3>

      <div class="cat-list" id="catList">
        <button class="pill-cat" data-cat="reservas"><i class="fa-solid fa-calendar-check"></i> {{ __('Bookings') }}</button>
        <button class="pill-cat" data-cat="pagos"><i class="fa-solid fa-credit-card"></i> {{ __('Payments') }}</button>
        <button class="pill-cat" data-cat="requisitos"><i class="fa-solid fa-id-card"></i> {{ __('Requirements') }}</button>
        <button class="pill-cat" data-cat="seguros"><i class="fa-solid fa-shield-halved"></i> {{ __('Insurance') }}</button>
        <button class="pill-cat" data-cat="entrega"><i class="fa-solid fa-car-side"></i> {{ __('Pick-up / Return') }}</button>
        <button class="pill-cat" data-cat="otros"><i class="fa-solid fa-circle-question"></i> {{ __('Other') }}</button>
      </div>

      <div class="kb-actions">
        <a id="btnWhats" class="btn btn-primary" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
      </div>

      <!-- Car widget andando -->
      <div class="car-widget">
        <div class="cw-head">
          <div>
            <div class="cw-title">{{ __('Ready to drive?') }}</div>
            <div class="cw-sub">{{ __('Explore the catalog and choose your ideal car') }}</div>
          </div>
          <i class="fa-solid fa-bolt cw-bolt"></i>
        </div>

        <div class="cw-scene">
          <div class="cw-road"><span></span><span></span><span></span><span></span></div>

          <!-- Auto -->
          <svg class="cw-car" viewBox="0 0 260 110" xmlns="http://www.w3.org/2000/svg" aria-label="Auto Viajero">
            <path d="M30 70 C45 40, 75 35, 120 35 H165 C185 35, 205 45, 215 60 L230 70 Q235 76 228 80 H45 C35 80 30 77 30 70Z"
                  fill="#b22222" stroke="#8b1d1a" stroke-width="3"/>
            <path d="M120 38 H162 C175 38 190 55 190 55 H120 Z" fill="#f2f6ff" opacity=".9" stroke="#d6e2ff" stroke-width="2"/>
            <path d="M80 50 Q100 40 120 38 V55 H90 Z" fill="#f2f6ff" opacity=".9" stroke="#d6e2ff" stroke-width="2"/>
            <circle cx="85" cy="80" r="18" fill="#1f2937"/>
            <circle cx="85" cy="80" r="10" fill="#111"/>
            <circle cx="200" cy="80" r="18" fill="#1f2937"/>
            <circle cx="200" cy="80" r="10" fill="#111"/>
            <rect x="222" y="68" width="10" height="6" rx="2" fill="#ffd166"/>
          </svg>
        </div>

        {{-- si tienes ruta del catálogo, usa route('catalogo') --}}
        <a href="{{ url('/catalogo') }}" class="btn btn-secondary btn-wide">
          <i class="fa-solid fa-car"></i> {{ __('View vehicle catalog') }}
        </a>
      </div>
      <!-- /car-widget -->
    </aside>

    <!-- Chat -->
    <div class="chat-card">
      <div class="chat-head">
        <div class="agent">
          <div class="avatar">V</div>
          <div>
            <div class="agent-name">{{ __('Viajero Bot') }}</div>
            <div class="agent-sub">{{ __('Instant answers · 24/7') }}</div>
          </div>
        </div>
        <button class="btn btn-secondary btn-agent" id="btnAgent">
          <i class="fa-solid fa-headset"></i> {{ __('Talk to an agent') }}
        </button>
      </div>

      <div class="chat-body" id="chatBody"></div>

     <div class="suggestions" id="suggestions">
  <button type="button" class="sg" data-q="{{ __('What documents do I need to rent a car?') }}">
    {{ __('What documents do I need?') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('How much is the security deposit?') }}">
    {{ __('Security deposit?') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('Can I pay in cash?') }}">
    {{ __('Pay in cash?') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('What does the insurance include?') }}">
    {{ __('What does insurance include?') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('How do I make a reservation?') }}">
    {{ __('How do I make a reservation?') }}
  </button>
</div>

      <form class="chat-input" id="chatForm" autocomplete="off">
  <input
    id="msg"
    type="text"
    placeholder="{{ __('Type your question… (e.g. how do I modify my reservation?)') }}"
  >

  <div class="input-actions">
    <button id="sendBtn" type="submit">
      {{ __('Send') }} <i class="fa-solid fa-paper-plane"></i>
    </button>

    <button type="button" id="clearChat" class="btn-clear-link">
      <i class="fa-solid fa-trash-can"></i> <strong>{{ __('Clear chat') }}</strong>
    </button>
  </div>
</form>
      </form>

      <div class="typing" id="typing"><span></span><span></span><span></span></div>
    </div>
  </section>
</main>
@endsection

@section('js-vistaFAQ')
  <script src="{{ asset('js/faq.js') }}"></script>
@endsection
