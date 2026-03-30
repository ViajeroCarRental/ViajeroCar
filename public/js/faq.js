(function () {
  const onReady = (fn) =>
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', fn, { once: true })
      : fn();

  // ============================================================
  // FUNCIÓN PARA OBTENER EL IDIOMA ACTUAL
  // ============================================================
  function getCurrentLocale() {
    // Primero intentar desde el HTML lang
    const htmlLang = document.documentElement.lang || 'es';
    // Si es 'en' devolver 'en', si no 'es'
    return htmlLang === 'en' ? 'en' : 'es';
  }

  // ============================================================
  // BASE DE CONOCIMIENTO BILINGÜE
  // ============================================================
  const KB = {
    es: [
      {
        q: ['documentos','requisitos','licencia','identificación','tarjeta'],
        cat: 'requisitos',
        a: `<strong>📄 Requisitos para rentar un auto</strong><br><br>
        Es necesario presentar en <strong>original</strong>:<br><br>
        1️⃣ <strong>Identificación oficial vigente</strong> (INE o Pasaporte).<br>
        2️⃣ <strong>Licencia de conducir vigente</strong> con al menos <strong>1 año de antigüedad</strong>.<br>
        3️⃣ <strong>Tarjeta de crédito</strong> a nombre del titular de la renta con <strong>1 año de antigüedad mínima</strong>.`
      },
      {
        q: ['depósito','garantía','bloqueo','retención'],
        cat: 'pagos',
        a: `<strong>💳 Depósito y garantía</strong><br><br>
        El depósito es <strong>obligatorio</strong> y se realiza mediante un <strong>bloqueo (pre-autorización)</strong>
        exclusivamente en tu <strong>Tarjeta de Crédito</strong> (Visa, Mastercard o American Express).<br><br>
        La garantía se realiza principalmente con <strong>tarjeta de crédito</strong>.
        En algunos casos específicos puede aceptarse <strong>tarjeta de débito</strong>.<br><br>
        💰 El monto depende del auto y la cobertura elegida; por ejemplo:<br>
        • Auto compacto con cobertura LDW: depósito desde <strong>$5,000 MXN</strong>.<br><br>
        ⚠️ No se aceptan depósitos en <strong>efectivo</strong> para la garantía.`
      },
      {
        q: ['efectivo','oxxo','mercado pago','paypal'],
        cat: 'pagos',
        a: `<strong>💵 Formas de pago</strong><br><br>
        Sí, aceptamos <strong>efectivo</strong> como forma de pago para cubrir el total de tu renta directamente en sucursal.<br><br>
        ⚠️ Importante: aunque pagues la renta en efectivo, la <strong>tarjeta de crédito para la garantía</strong>
        sigue siendo un requisito obligatorio.<br><br>
        También puedes pagar con:<br>
        • <strong>OXXO</strong><br>
        • <strong>Mercado Pago</strong><br>
        • <strong>PayPal</strong>`
      },
      {
        q: ['seguro','cobertura','daños','responsabilidad'],
        cat: 'seguros',
        a: `<strong>🛡️ Seguros y coberturas</strong><br><br>
        Nuestras tarifas estándar incluyen <strong>Protección de Responsabilidad Civil (LI)</strong>
        contra daños a terceros hasta por <strong>$350,000 MXN</strong>.<br><br>
        Dependiendo del paquete contratado, puedes contar con protección por daños
        (<strong>LDW / CDW</strong>) que reduce tu responsabilidad económica.<br><br>
        Te recomendamos consultar en mostrador nuestros <strong>paquetes de Protección Total</strong>
        para viajar con mayor tranquilidad.`
      },
      {
        q: ['horario','entregar','devolver','aeropuerto','pick up','drop off'],
        cat: 'entrega',
        a: `<strong>📍 Entrega y devolución</strong><br><br>
        ⏰ <strong>Horario habitual:</strong><br>
        8:00 a 22:00 h <em>(sujeto a disponibilidad)</em>.<br><br>
        📌 Entregamos y recibimos unidades en:<br>
        • <strong>Central Park Querétaro</strong><br>
        • <strong>Aeropuerto Internacional de Querétaro (QRO)</strong><br>
        • <strong>Central de Autobuses de Querétaro (TAQ)</strong><br><br>
        ⚠️ <strong>Importante:</strong><br>
        El auto debe devolverse <strong>limpio</strong> y con el <strong>tanque lleno</strong>.<br>
        Fumar dentro del vehículo o entregarlo con suciedad excesiva genera un cargo
        de <strong>$4,000 MXN</strong> por limpieza profunda.`
      },
      {
        q: ['modificar','cancelar','cambiar','reprogramar','reembolso'],
        cat: 'reservas',
        a: `<strong>📝 Modificaciones y cancelaciones</strong><br><br>
        Puedes modificar o cancelar tu reserva desde <strong>"Mi reserva"</strong>.<br><br>
        Las cancelaciones están sujetas a la <strong>política vigente</strong> según la anticipación.<br><br>
        También puedes contratar un <strong>seguro de cancelación</strong>
        para obtener un reembolso total.`
      },
      {
        q: ['edad','25','jóvenes','menor','años','requisitos edad','cuantos años'],
        cat: 'requisitos',
        a: `<strong>🎂 Requisitos de edad</strong><br><br>
        Edad estándar: <strong>25 años</strong>.<br>
        Edad mínima permitida: <strong>21 años</strong>.<br><br>
        ⚠️ De <strong>21 a 24 años</strong> aplica cargo de <strong>conductor joven</strong>
        y coberturas adicionales.`
      },
      {
        q: ['reservar','reservación','reservaciones','rentar','alquilar'],
        cat: 'reservas',
        a: `<strong>🚗 Iniciar reservación</strong><br><br>
        👉 <a href="/reservaciones" target="_blank"><strong>Iniciar reservación</strong></a><br><br>
        💬 Si necesitas ayuda personalizada:<br>
        <a href="https://wa.me/524427169793" target="_blank"><strong>WhatsApp</strong></a>`
      }
    ],
    en: [
      {
        q: ['documents','requirements','license','id','identification','card'],
        cat: 'requisitos',
        a: `<strong>📄 Requirements to rent a car</strong><br><br>
        You need to present the following <strong>original</strong> documents:<br><br>
        1️⃣ <strong>Valid official ID</strong> (INE or Passport).<br>
        2️⃣ <strong>Valid driver's license</strong> with at least <strong>1 year of seniority</strong>.<br>
        3️⃣ <strong>Credit card</strong> in the renter's name with <strong>at least 1 year of seniority</strong>.`
      },
      {
        q: ['deposit','security deposit','hold','authorization','block'],
        cat: 'pagos',
        a: `<strong>💳 Security deposit</strong><br><br>
        The deposit is <strong>mandatory</strong> and is made through a <strong>pre-authorization hold</strong>
        exclusively on your <strong>Credit Card</strong> (Visa, Mastercard or American Express).<br><br>
        The deposit is primarily made with a <strong>credit card</strong>.
        In some specific cases, a <strong>debit card</strong> may be accepted.<br><br>
        💰 The amount depends on the vehicle and coverage selected; for example:<br>
        • Compact car with LDW coverage: deposit from <strong>$5,000 MXN</strong>.<br><br>
        ⚠️ <strong>Cash deposits are not accepted</strong> for the guarantee.`
      },
      {
        q: ['cash','oxxo','mercado pago','paypal','payment'],
        cat: 'pagos',
        a: `<strong>💵 Payment methods</strong><br><br>
        Yes, we accept <strong>cash</strong> as a payment method to cover the total rental amount directly at the branch.<br><br>
        ⚠️ Important: even if you pay the rental in cash, a <strong>credit card for the security deposit</strong>
        is still a mandatory requirement.<br><br>
        You can also pay with:<br>
        • <strong>OXXO</strong><br>
        • <strong>Mercado Pago</strong><br>
        • <strong>PayPal</strong>`
      },
      {
        q: ['insurance','coverage','damage','liability','protection'],
        cat: 'seguros',
        a: `<strong>🛡️ Insurance and coverage</strong><br><br>
        Our standard rates include <strong>Liability Insurance (LI)</strong>
        covering third-party damages up to <strong>$350,000 MXN</strong>.<br><br>
        Depending on the package selected, you may have <strong>LDW / CDW</strong> protection
        which reduces your financial liability.<br><br>
        We recommend asking at the counter about our <strong>Total Protection packages</strong>
        for greater peace of mind.`
      },
      {
        q: ['schedule','hours','pick up','drop off','airport','delivery','return'],
        cat: 'entrega',
        a: `<strong>📍 Pick-up and return</strong><br><br>
        ⏰ <strong>Regular hours:</strong><br>
        8:00 AM – 10:00 PM <em>(subject to availability)</em>.<br><br>
        📌 We deliver and receive vehicles at:<br>
        • <strong>Central Park Querétaro</strong><br>
        • <strong>Querétaro International Airport (QRO)</strong><br>
        • <strong>Querétaro Bus Station (TAQ)</strong><br><br>
        ⚠️ <strong>Important:</strong><br>
        The vehicle must be returned <strong>clean</strong> and with a <strong>full tank</strong>.<br>
        Smoking inside the vehicle or returning it with excessive dirt will result in a
        <strong>$4,000 MXN</strong> deep cleaning fee.`
      },
      {
        q: ['modify','cancel','change','reschedule','refund','reservation'],
        cat: 'reservas',
        a: `<strong>📝 Modifications and cancellations</strong><br><br>
        You can modify or cancel your reservation from <strong>"My reservation"</strong>.<br><br>
        Cancellations are subject to the <strong>current policy</strong> based on advance notice.<br><br>
        You can also purchase <strong>cancellation insurance</strong>
        to receive a full refund.`
      },
      {
        q: ['age','25','young','minor','years','age requirement','how old'],
        cat: 'requisitos',
        a: `<strong>🎂 Age requirements</strong><br><br>
        Standard age: <strong>25 years</strong>.<br>
        Minimum age allowed: <strong>21 years</strong>.<br><br>
        ⚠️ From <strong>21 to 24 years</strong>, a <strong>young driver fee</strong> applies
        along with additional coverage requirements.`
      },
      {
        q: ['book','booking','reservation','rent','reserve','make a reservation'],
        cat: 'reservas',
        a: `<strong>🚗 Make a reservation</strong><br><br>
        👉 <a href="/reservaciones" target="_blank"><strong>Start your reservation</strong></a><br><br>
        💬 If you need personalized help:<br>
        <a href="https://wa.me/524427169793" target="_blank"><strong>WhatsApp</strong></a>`
      }
    ]
  };

  // Función para obtener KB según idioma
  function getKB() {
    const locale = getCurrentLocale();
    return KB[locale] || KB.es;
  }

  // Función para obtener mensajes de bienvenida según idioma
  function getWelcomeMessage() {
    const locale = getCurrentLocale();
    if (locale === 'en') {
      return `Hello! 👋 I'm your <strong>Viajero Car Rental</strong> assistant.
I can help you with <strong>bookings, payments, requirements, insurance</strong> and <strong>pick-up/return</strong>.

📅 Ready to book now?
👉 <a href="/reservaciones">Start your reservation</a>

Use the buttons below 👇`;
    }
    return `¡Hola! 👋 Soy tu asistente de <strong>Viajero Car Rental</strong>.
Puedo ayudarte con <strong>reservas, pagos, requisitos, seguros</strong> y <strong>entrega/devolución</strong>.

📅 ¿Quieres reservar ahora?
👉 <a href="/reservaciones">Iniciar reservación</a>

Usa los botones de abajo 👇`;
  }

  // Función para encontrar respuesta según idioma
  function findAnswer(text, catHint=null){
    const t = (text||'').toLowerCase();
    const kb = getKB();
    const items = catHint ? kb.filter(k=>k.cat===catHint) : kb;
    for(const item of items){
      if(item.q.some(k => t.includes(k))) return item.a;
    }
    const locale = getCurrentLocale();
    if (locale === 'en') {
      return `I couldn't find an exact answer 🤔. Can you give me more details?
If you prefer, I can connect you with a human agent.`;
    }
    return `No encontré una respuesta exacta 🤔. ¿Puedes darme más detalles?
Si prefieres, puedo conectarte con un agente humano.`;
  }

  // Función para obtener el texto de agente según idioma
  function getAgentMessages() {
    const locale = getCurrentLocale();
    const waNumber = '524427169793';
    if (locale === 'en') {
      return {
        user: 'I want to speak with an agent.',
        bot: `Sure 🙌. You can contact us via WhatsApp or phone:
• WhatsApp: <a href="https://wa.me/${waNumber}" target="_blank" rel="noopener">+52 442 716 9793</a>
• Phone: <a href="tel:+52442 716 9793">+52 442 716 9793</a>`
      };
    }
    return {
      user: 'Quiero hablar con un agente.',
      bot: `Con gusto 🙌. Puedes escribirnos a WhatsApp o llamarnos:
• WhatsApp: <a href="https://wa.me/${waNumber}" target="_blank" rel="noopener">+52 442 716 9793</a>
• Teléfono: <a href="tel:+52442 716 9793">+52 442 716 9793</a>`
    };
  }

  (function(){
    const AUTH_KEY = 'vj_auth';
     const URLS = { LOGIN:'/login', PROFILE:'/perfil' };
    if(!window.VJ_AUTH){
      function getAuth(){ try{ return JSON.parse(localStorage.getItem(AUTH_KEY)||'null'); }catch(e){ return null; } }
      function isLogged(){ return !!localStorage.getItem(AUTH_KEY); }
      window.VJ_AUTH = { getAuth, isLogged, URLS };
    }
    onReady(() => {
      const link = document.getElementById('accountLink');
      if(!link) return;
      const locale = getCurrentLocale();
      if(window.VJ_AUTH.isLogged()){
        const u = window.VJ_AUTH.getAuth() || {};
        link.href = URLS.PROFILE;
        link.title = locale === 'en' ? 'My profile' : 'Mi perfil';
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
      }else{
        link.href = URLS.LOGIN;
        link.title = locale === 'en' ? 'Sign in' : 'Iniciar sesión';
        link.innerHTML = '<i class="fa-regular fa-user"></i>';
      }
      window.addEventListener('storage', e=>{ if(e.key===AUTH_KEY) location.reload(); });
    });
  })();

  onReady(() => {
    const qs  = (s)=>document.querySelector(s);
    const qsa = (s)=>[...document.querySelectorAll(s)];
    const wait = (ms)=>new Promise(r=>setTimeout(r,ms));

    // Navbar (todo opcional)
    const topbar = qs('.topbar');
    function toggleTopbar(){
      if(!topbar) return;
      window.scrollY>40 ? topbar.classList.add('solid') : topbar.classList.remove('solid');
    }
    toggleTopbar();
    window.addEventListener('scroll', toggleTopbar, {passive:true});

    const hamburger = qs('.hamburger');
    const menu = qs('.menu');
    if (hamburger && menu){
      hamburger.addEventListener('click', ()=>{
        const show = getComputedStyle(menu).display==='none';
        menu.style.display = show ? 'flex' : 'none';
        if (show){ menu.style.flexDirection='column'; menu.style.gap='12px'; }
      });
    }

    // Link activo
    qsa('.menu a').forEach(a=>{
      const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
      const href = (a.getAttribute('href')||'').toLowerCase();
      a.classList.toggle('active', href===current);
    });

    // Año footer (si existe)
    const yearEl = qs('#year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // WhatsApp (si existe)
    const waNumber = '524427169793';
    const btnWhats = qs('#btnWhats');
    if (btnWhats){
      const locale = getCurrentLocale();
      const waText = locale === 'en' ? 'Hello, I need help from the Help Center.' : 'Hola, necesito ayuda desde el Centro de Ayuda.';
      btnWhats.href = `https://wa.me/${waNumber}?text=${encodeURIComponent(waText)}`;
      btnWhats.target = '_blank';
      btnWhats.rel = 'noopener';
    }

    // ===== Chat =====
    const chatBody = qs('#chatBody');
    const typing = qs('#typing');
    const form = qs('#chatForm');
    const input = qs('#msg');
    const STORAGE_KEY = 'faq_viajero_chat';

    if(!chatBody || !form || !input){
      return;
    }

    function scrollBottom(){ chatBody.scrollTop = chatBody.scrollHeight; }
    function bubble(text, from='bot'){
      const row = document.createElement('div');
      row.className = `msg ${from==='user'?'user':'bot'}`;
      row.innerHTML = from==='user'
        ? `<div class="bubble">${text}</div>`
        : `<div class="avatar-sm">V</div><div class="bubble">${text}</div>`;
      chatBody.appendChild(row); scrollBottom();
    }
    function setTyping(on){ if(typing){ typing.style.display = on? 'block' : 'none'; scrollBottom(); } }
    function saveHistory(){ localStorage.setItem(STORAGE_KEY, chatBody.innerHTML); }
    function loadHistory(){
      const html = localStorage.getItem(STORAGE_KEY);
      if(html){ chatBody.innerHTML = html; scrollBottom(); }
      else { bubble(getWelcomeMessage()); }
    }
    function clearHistory(){
      localStorage.removeItem(STORAGE_KEY);
      chatBody.innerHTML='';
      bubble(getWelcomeMessage());
    }

    const btnClear = qs('#clearChat');
    if (btnClear) btnClear.addEventListener('click', clearHistory);

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const msg = (input.value || '').trim();
      if(!msg) return;
      bubble(msg,'user'); input.value=''; saveHistory();
      setTyping(true); await wait(500);
      const a = findAnswer(msg);
      setTyping(false); bubble(a,'bot'); saveHistory();
    });

    // Sugerencias
    qsa('.sg').forEach(b => {
      b.addEventListener('click', (e) => {
        e.preventDefault();
        const text = b.dataset.q || b.textContent.trim();
        if (!text) return;

        bubble(text, 'user');
        saveHistory();
        setTyping(true);

        setTimeout(() => {
          const a = findAnswer(text);
          setTyping(false);
          bubble(a, 'bot');
          saveHistory();
        }, 400);
      });
    });

    // Categorías
    qsa('.pill-cat').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const cat = btn.dataset.cat;
        const catText = btn.textContent.trim();
        bubble(`<i class="fa-solid fa-tag"></i> ${getCurrentLocale() === 'en' ? `I want to know about <strong>${catText}</strong>.` : `Quiero saber sobre <strong>${catText}</strong>.`}`,'user');
        setTyping(true); await wait(400);
        const kb = getKB();
        const example = kb.find(k=>k.cat===cat);
        setTyping(false);
        if (example) {
          bubble(example.a,'bot');
        } else {
          const msg = getCurrentLocale() === 'en' ? 'Tell me what you need about this category.' : 'Cuéntame qué necesitas de esta categoría.';
          bubble(msg,'bot');
        }
        saveHistory();
      });
    });

    // Agente humano
    const btnAgent = qs('#btnAgent');
    if (btnAgent){
      btnAgent.addEventListener('click', ()=>{
        const messages = getAgentMessages();
        bubble(messages.user, 'user');
        bubble(messages.bot, 'bot');
        saveHistory();
      });
    }

    loadHistory();
  });
})();
