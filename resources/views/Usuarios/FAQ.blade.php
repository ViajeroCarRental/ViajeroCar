@extends('layouts.Usuarios')

@section('Titulo', __('messages.faq_titulo'))

@section('css-vistaFAQ')
  <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
@endsection

@section('contenidoFAQ')
<main class="page">
  <!-- HERO -->
  <section class="hero hero-mini">
    <div class="hero-bg">
      <img src="{{ asset('img/faq.jpg') }}" alt="{{ __('messages.centro_ayuda') }}">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content left">
      <div class="hero-text">
        <h1>{{ __('messages.centro_ayuda') }}</h1>
        <p>{{ __('messages.resuelve_dudas') }}</p>
      </div>
    </div>
  </section>


  <!-- FAQ -->
  <section class="faq-wrap">
    <aside class="faq-side">
      <h3><i class="fa-solid fa-folder-open"></i> {{ __('messages.categorias') }}</h3>

      <div class="cat-list" id="catList">
        <button class="pill-cat" data-cat="reservas"><i class="fa-solid fa-calendar-check"></i> {{ __('messages.reservas') }}</button>
        <button class="pill-cat" data-cat="pagos"><i class="fa-solid fa-credit-card"></i> {{ __('messages.pagos') }}</button>
        <button class="pill-cat" data-cat="requisitos"><i class="fa-solid fa-id-card"></i> {{ __('messages.requisitos') }}</button>
        <button class="pill-cat" data-cat="seguros"><i class="fa-solid fa-shield-halved"></i> {{ __('messages.seguros') }}</button>
        <button class="pill-cat" data-cat="entrega"><i class="fa-solid fa-car-side"></i> {{ __('messages.entrega_devolucion') }}</button>
        <button class="pill-cat" data-cat="otros"><i class="fa-solid fa-circle-question"></i> {{ __('messages.otros') }}</button>
      </div>

      <div class="kb-actions">
        <a id="btnWhats" class="btn btn-primary" target="_blank"><i class="fa-brands fa-whatsapp"></i> {{ __('messages.whatsapp') }}</a>
      </div>

      <!-- Car widget andando -->
      <div class="car-widget">
        <div class="cw-head">
          <div>
            <div class="cw-title">{{ __('messages.listo_para_menejar') }}</div>
            <div class="cw-sub">{{ __('messages.explora_catalogo') }}</div>
          </div>
          <i class="fa-solid fa-bolt cw-bolt"></i>
        </div>

        <div class="cw-scene">
          <div class="cw-road"><span></span><span></span><span></span><span></span></div>

          <!-- Auto -->
          <svg class="cw-car" viewBox="0 0 260 110" xmlns="http://www.w3.org/2000/svg" aria-label="{{ __('messages.auto_viajero') }}">
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
          <i class="fa-solid fa-car"></i> {{ __('messages.ver_catalogo') }}
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
            <div class="agent-name">{{ __('messages.viajero_bot') }}</div>
            <div class="agent-sub">{{ __('messages.respuestas_inmediatas') }}</div>
          </div>
        </div>
        <button class="btn btn-secondary btn-agent" id="btnAgent">
          <i class="fa-solid fa-headset"></i> {{ __('messages.hablar_con_agente') }}
        </button>
      </div>

      <div class="chat-body" id="chatBody"></div>

     <div class="suggestions" id="suggestions">
  <button type="button" class="sg" data-q="{{ __('messages.que_documentos_necesito') }}">
    {{ __('messages.que_documentos_necesito') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('messages.deposito_garantia') }}">
    {{ __('messages.deposito_garantia') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('messages.pagar_efectivo') }}">
    {{ __('messages.pagar_efectivo') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('messages.que_incluye_seguro') }}">
    {{ __('messages.que_incluye_seguro') }}
  </button>
  <button type="button" class="sg" data-q="{{ __('messages.como_hacer_reservacion') }}">
    {{ __('messages.como_hacer_reservacion') }}
  </button>
</div>

      <form class="chat-input" id="chatForm" autocomplete="off">
  <input
    id="msg"
    type="text"
    placeholder="{{ __('messages.escribe_pregunta') }}"
  >

  <div class="input-actions">
    <button id="sendBtn" type="submit">
      {{ __('messages.enviar') }} <i class="fa-solid fa-paper-plane"></i>
    </button>

    <button type="button" id="clearChat" class="btn-clear-link">
      <i class="fa-solid fa-trash-can"></i> <strong>{{ __('messages.limpiar_chat') }}</strong>
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
  <script>
    window.faqTranslations = {
        // Navbar
        mi_perfil: "{{ __('messages.mi_perfil') }}",
        iniciar_sesion: "{{ __('messages.iniciar_sesion') }}",
        whatsapp: "{{ __('messages.whatsapp') }}",

        // Respuestas del chat
        faq_documentos_titulo: "{{ __('messages.faq_documentos_titulo') }}",
        faq_documentos_texto: "{{ __('messages.faq_documentos_texto') }}",
        faq_identificacion: "{{ __('messages.faq_identificacion') }}",
        faq_licencia: "{{ __('messages.faq_licencia') }}",
        faq_tarjeta_credito: "{{ __('messages.faq_tarjeta_credito') }}",

        faq_deposito_titulo: "{{ __('messages.faq_deposito_titulo') }}",
        faq_deposito_obligatorio: "{{ __('messages.faq_deposito_obligatorio') }}",
        faq_tarjeta_credito_exclusiva: "{{ __('messages.faq_tarjeta_credito_exclusiva') }}",
        faq_garantia_principal: "{{ __('messages.faq_garantia_principal') }}",
        faq_debito_aceptado: "{{ __('messages.faq_debito_aceptado') }}",
        faq_monto_depende: "{{ __('messages.faq_monto_depende') }}",
        faq_ejemplo_compacto: "{{ __('messages.faq_ejemplo_compacto') }}",
        faq_no_efectivo: "{{ __('messages.faq_no_efectivo') }}",

        faq_formas_pago_titulo: "{{ __('messages.faq_formas_pago_titulo') }}",
        faq_efectivo_aceptado: "{{ __('messages.faq_efectivo_aceptado') }}",
        faq_importante_tarjeta: "{{ __('messages.faq_importante_tarjeta') }}",
        faq_tambien_puedes: "{{ __('messages.faq_tambien_puedes') }}",

        faq_seguro_titulo: "{{ __('messages.faq_seguro_titulo') }}",
        faq_li_incluido: "{{ __('messages.faq_li_incluido') }}",
        faq_paquetes_danos: "{{ __('messages.faq_paquetes_danos') }}",
        faq_recomendacion_proteccion: "{{ __('messages.faq_recomendacion_proteccion') }}",

        faq_entrega_titulo: "{{ __('messages.faq_entrega_titulo') }}",
        faq_horario_titulo: "{{ __('messages.faq_horario_titulo') }}",
        faq_horario: "{{ __('messages.faq_horario') }}",
        faq_sujeto_disponibilidad: "{{ __('messages.faq_sujeto_disponibilidad') }}",
        faq_entregamos_en: "{{ __('messages.faq_entregamos_en') }}",
        faq_central_park: "{{ __('messages.faq_central_park') }}",
        faq_aeropuerto_qro: "{{ __('messages.faq_aeropuerto_qro') }}",
        faq_central_autobuses: "{{ __('messages.faq_central_autobuses') }}",
        faq_importante: "{{ __('messages.faq_importante') }}",
        faq_devolver_limpio: "{{ __('messages.faq_devolver_limpio') }}",
        faq_cargo_limpieza: "{{ __('messages.faq_cargo_limpieza') }}",

        faq_modificaciones_titulo: "{{ __('messages.faq_modificaciones_titulo') }}",
        faq_modificar_desde: "{{ __('messages.faq_modificar_desde') }}",
        faq_mi_reserva: "{{ __('messages.faq_mi_reserva') }}",
        faq_cancelaciones_sujetas: "{{ __('messages.faq_cancelaciones_sujetas') }}",
        faq_seguro_cancelacion: "{{ __('messages.faq_seguro_cancelacion') }}",

        faq_edad_titulo: "{{ __('messages.faq_edad_titulo') }}",
        faq_edad_estandar: "{{ __('messages.faq_edad_estandar') }}",
        faq_anos: "{{ __('messages.faq_anos') }}",
        faq_edad_minima: "{{ __('messages.faq_edad_minima') }}",
        faq_conductor_joven: "{{ __('messages.faq_conductor_joven') }}",

        faq_iniciar_reservacion_titulo: "{{ __('messages.faq_iniciar_reservacion_titulo') }}",
        faq_iniciar_reservacion: "{{ __('messages.faq_iniciar_reservacion') }}",
        faq_ayuda_personalizada: "{{ __('messages.faq_ayuda_personalizada') }}",

        // Mensajes del sistema
        faq_bienvenida: "{{ __('messages.faq_bienvenida') }}",
        faq_bienvenida_2: "{{ __('messages.faq_bienvenida_2') }}",
        faq_no_encontre: "{{ __('messages.faq_no_encontre') }}",
        faq_quiero_saber: "{{ __('messages.faq_quiero_saber') }}",
        faq_categoria_sin_respuesta: "{{ __('messages.faq_categoria_sin_respuesta') }}",
        faq_agente_mensaje: "{{ __('messages.faq_agente_mensaje') }}",
        faq_agente_respuesta: "{{ __('messages.faq_agente_respuesta', ['numero' => '524427169793']) }}",
        whatsapp_mensaje: "{{ __('messages.whatsapp_mensaje') }}"
    };
  </script>
  <script src="{{ asset('js/faq.js') }}"></script>
@endsection
