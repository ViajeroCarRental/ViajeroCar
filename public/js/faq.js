(function () {
  const onReady = (fn) =>
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', fn, { once: true })
      : fn();

  // ====== AUTH (icono) ======
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
      if(window.VJ_AUTH.isLogged()){
        const u = window.VJ_AUTH.getAuth() || {};
        link.href = URLS.PROFILE;
        link.title = 'Mi perfil';
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
      }else{
        link.href = URLS.LOGIN;
        link.title = 'Iniciar sesión';
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
      btnWhats.href = `https://wa.me/${waNumber}?text=${encodeURIComponent('Hola, necesito ayuda desde el Centro de Ayuda.')}`;
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
      // Si no hay chat en esta vista, salir sin romper
      return;
    }

    const KB = [
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
    <a href="https://wa.me/${waNumber}" target="_blank"><strong>WhatsApp</strong></a>`
  }
];

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
     else { bubble(`¡Hola! Soy tu asistente de Viajero. Pregúntame sobre <strong>reservas, pagos, requisitos, seguros</strong> o <strong>entrega/devolución</strong>. También puedes usar los atajos de abajo 👇`); }
    }
    function clearHistory(){
      localStorage.removeItem(STORAGE_KEY);
      chatBody.innerHTML='';
      bubble(`¡Hola! 👋 Soy tu asistente de <strong>Viajero Car Rental</strong>.
Puedo ayudarte con <strong>reservas, pagos, requisitos, seguros</strong> y <strong>entrega/devolución</strong>.

📅 ¿Quieres reservar ahora?
👉 <a href="/reservaciones">Iniciar reservación</a>

Usa los botones de abajo 👇`);

    }

    const btnClear = qs('#clearChat');
    if (btnClear) btnClear.addEventListener('click', clearHistory);

    function findAnswer(text, catHint=null){
      const t = (text||'').toLowerCase();
      const items = catHint ? KB.filter(k=>k.cat===catHint) : KB;
      for(const item of items){ if(item.q.some(k => t.includes(k))) return item.a; }
      return `No encontré una respuesta exacta 🤔. ¿Puedes darme más detalles?
Si prefieres, puedo conectarte con un agente humano.`;
    }

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
    e.preventDefault(); // 🔥 evita submit real
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
        bubble(`<i class="fa-solid fa-tag"></i> Quiero saber sobre <strong>${btn.textContent.trim()}</strong>.`,'user');
        setTyping(true); await wait(400);
        const example = KB.find(k=>k.cat===cat);
        setTyping(false); bubble(example ? example.a : 'Cuéntame qué necesitas de esta categoría.','bot'); saveHistory();
      });
    });

    // Agente humano (si existe boton)
    const btnAgent = qs('#btnAgent');
    if (btnAgent){
      btnAgent.addEventListener('click', ()=>{
        bubble('Quiero hablar con un agente.','user');
         bubble(`Con gusto 🙌. Puedes escribirnos a WhatsApp o llamarnos:
• WhatsApp: <a href="https://wa.me/${waNumber}" target="_blank" rel="noopener">+52 442 716 9793</a>
• Teléfono: <a href="tel:+52442 716 9793">+52 442 716 9793</a>`, 'bot');
        saveHistory();
      });
    }

    loadHistory();
  });
})();
