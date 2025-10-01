@extends('layouts.Usuarios')

 @section('Titulo','F.A.Q | ViajeroCar')

 @section('css-vistaFAQ')
     <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
 @endsection

 @section('contenidoFAQ')

 <main class="page">
    <!-- HERO -->
    <section class="hero hero-mini">
      <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=1600&auto=format&fit=crop" alt="">
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Centro de ayuda <span>Viajero</span></h1>
        <p>Resuelve tus dudas conversando con nuestro asistente</p>
      </div>
    </section>

    <!-- CHAT + SIDE -->
    <section class="faq-wrap">
      <aside class="faq-side">
        <h3><i class="fa-solid fa-folder-open"></i> Categorías</h3>

        <div class="cat-list" id="catList">
          <button class="pill-cat" data-cat="reservas"><i class="fa-solid fa-calendar-check"></i> Reservas</button>
          <button class="pill-cat" data-cat="pagos"><i class="fa-solid fa-credit-card"></i> Pagos</button>
          <button class="pill-cat" data-cat="requisitos"><i class="fa-solid fa-id-card"></i> Requisitos</button>
          <button class="pill-cat" data-cat="seguros"><i class="fa-solid fa-shield-halved"></i> Seguros</button>
          <button class="pill-cat" data-cat="entrega"><i class="fa-solid fa-car-side"></i> Entrega/Devolución</button>
          <button class="pill-cat" data-cat="otros"><i class="fa-solid fa-circle-question"></i> Otros</button>
        </div>

        <div class="kb-actions">
          <button id="btnClear" class="btn btn-ghost"><i class="fa-solid fa-broom"></i> Limpiar chat</button>
          <a id="btnWhats" class="btn btn-primary" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
        </div>

        <!-- 🏎️ Carrito animado -->
        <div class="car-widget">
          <div class="cw-head">
            <div>
              <div class="cw-title">¿Listo para manejar?</div>
              <div class="cw-sub">Explora el catálogo y elige tu auto ideal</div>
            </div>
            <i class="fa-solid fa-bolt cw-bolt"></i>
          </div>

          <div class="cw-scene">
            <div class="cw-road"><span></span><span></span><span></span><span></span></div>

            <!-- Auto (SVG) -->
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

          <a href="catalogo.html" class="btn btn-secondary btn-wide"><i class="fa-solid fa-car"></i> Ver catálogo de autos</a>
        </div>
        <!-- /car-widget -->
      </aside>

      <div class="chat-card">
        <div class="chat-head">
          <div class="agent">
            <div class="avatar">V</div>
            <div>
              <div class="agent-name">Viajero Bot</div>
              <div class="agent-sub">Respuestas inmediatas · 24/7</div>
            </div>
          </div>
          <button class="btn btn-secondary btn-agent" id="btnAgent"><i class="fa-solid fa-headset"></i> Hablar con un agente</button>
        </div>

        <div class="chat-body" id="chatBody"></div>

        <div class="suggestions" id="suggestions">
          <button class="sg" data-q="¿Qué documentos necesito para rentar?">¿Qué documentos necesito?</button>
          <button class="sg" data-q="¿Cuánto es el depósito en garantía?">¿Depósito en garantía?</button>
          <button class="sg" data-q="¿Puedo pagar en efectivo?">¿Pagar en efectivo?</button>
          <button class="sg" data-q="¿Qué incluye el seguro?">¿Qué incluye el seguro?</button>
        </div>

        <form class="chat-input" id="chatForm">
          <input id="msg" placeholder="Escribe tu pregunta… (ej. ¿cómo modifico mi reserva?)" autocomplete="off">
          <button class="btn btn-primary" aria-label="enviar"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="typing" id="typing"><span></span><span></span><span></span></div>
      </div>
    </section>
  </main>

 @section('js-vistaFAQ')
     <script src="{{ asset('js/faq.js') }}"></script>

 @endsection
