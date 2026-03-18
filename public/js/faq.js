(function () {
  const onReady = (fn) =>
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', fn, { once: true })
      : fn();

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
        link.title = window.faqTranslations?.mi_perfil || 'Mi perfil';
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
      }else{
        link.href = URLS.LOGIN;
        link.title = window.faqTranslations?.iniciar_sesion || 'Iniciar sesión';
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
      btnWhats.href = `https://wa.me/${waNumber}?text=${encodeURIComponent(window.faqTranslations?.whatsapp_mensaje || 'Hola, necesito ayuda desde el Centro de Ayuda.')}`;
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

    const t = window.faqTranslations || {};

const KB = [
  {
    q: ['documentos','requisitos','licencia','identificación','tarjeta'],
    cat: 'requisitos',
    a: `<strong>📄 ${t.faq_documentos_titulo || 'Documentos necesarios para rentar un auto'}</strong><br><br>
    ${t.faq_documentos_texto || 'Es necesario presentar en original:'}<br><br>
    1️⃣ <strong>${t.faq_identificacion || 'Identificación oficial vigente (INE o Pasaporte).'}</strong><br>
    2️⃣ <strong>${t.faq_licencia || 'Licencia de conducir vigente con al menos 1 año de antigüedad.'}</strong><br>
    3️⃣ <strong>${t.faq_tarjeta_credito || 'Tarjeta de crédito a nombre del titular de la renta con 1 año de antigüedad mínima.'}</strong>`
  },

  {
    q: ['depósito','garantía','bloqueo','retención'],
    cat: 'pagos',
    a: `<strong>💳 ${t.faq_deposito_titulo || 'Depósito y garantía'}</strong><br><br>
    ${t.faq_deposito_obligatorio || 'El depósito es obligatorio y se realiza mediante un bloqueo (pre-autorización) exclusivamente en tu '}
    <strong>${t.faq_tarjeta_credito_exclusiva || 'Tarjeta de Crédito'}</strong> (Visa, Mastercard o American Express).<br><br>
    ${t.faq_garantia_principal || 'La garantía se realiza principalmente con tarjeta de crédito.'}
    ${t.faq_debito_aceptado || 'En algunos casos específicos puede aceptarse tarjeta de débito.'}<br><br>
    💰 ${t.faq_monto_depende || 'El monto depende del auto y la cobertura elegida; por ejemplo:'}<br>
    • ${t.faq_ejemplo_compacto || 'Auto compacto con cobertura LDW: depósito desde'} <strong>$5,000 MXN</strong>.<br><br>
    ⚠️ ${t.faq_no_efectivo || 'No se aceptan depósitos en efectivo para la garantía.'}`
  },

  {
    q: ['efectivo','oxxo','mercado pago','paypal'],
    cat: 'pagos',
    a: `<strong>💵 ${t.faq_formas_pago_titulo || 'Formas de pago'}</strong><br><br>
    ${t.faq_efectivo_aceptado || 'Sí, aceptamos efectivo como forma de pago para cubrir el total de tu renta directamente en sucursal.'}<br><br>
    ⚠️ ${t.faq_importante_tarjeta || 'Importante: aunque pagues la renta en efectivo, la tarjeta de crédito para la garantía sigue siendo un requisito obligatorio.'}<br><br>
    ${t.faq_tambien_puedes || 'También puedes pagar con:'}<br>
    • <strong>OXXO</strong><br>
    • <strong>Mercado Pago</strong><br>
    • <strong>PayPal</strong>`
  },

  {
    q: ['seguro','cobertura','daños','responsabilidad'],
    cat: 'seguros',
    a: `<strong>🛡️ ${t.faq_seguro_titulo || 'Seguros y coberturas'}</strong><br><br>
    ${t.faq_li_incluido || 'Nuestras tarifas estándar incluyen Protección de Responsabilidad Civil (LI) contra daños a terceros hasta por'} <strong>$350,000 MXN</strong>.<br><br>
    ${t.faq_paquetes_danos || 'Dependiendo del paquete contratado, puedes contar con protección por daños (LDW / CDW) que reduce tu responsabilidad económica.'}<br><br>
    ${t.faq_recomendacion_proteccion || 'Te recomendamos consultar en mostrador nuestros paquetes de Protección Total para viajar con mayor tranquilidad.'}`
  },

  {
    q: ['horario','entregar','devolver','aeropuerto','pick up','drop off'],
    cat: 'entrega',
    a: `<strong>📍 ${t.faq_entrega_titulo || 'Entrega y devolución'}</strong><br><br>
    ⏰ <strong>${t.faq_horario_titulo || 'Horario habitual:'}</strong><br>
    ${t.faq_horario || '8:00 a 22:00 h'} <em>${t.faq_sujeto_disponibilidad || 'sujeto a disponibilidad'}</em>.<br><br>
    📌 ${t.faq_entregamos_en || 'Entregamos y recibimos unidades en:'}<br>
    • <strong>${t.faq_central_park || 'Central Park Querétaro'}</strong><br>
    • <strong>${t.faq_aeropuerto_qro || 'Aeropuerto Internacional de Querétaro (QRO)'}</strong><br>
    • <strong>${t.faq_central_autobuses || 'Central de Autobuses de Querétaro (TAQ)'}</strong><br><br>
    ⚠️ <strong>${t.faq_importante || 'Importante:'}</strong><br>
    ${t.faq_devolver_limpio || 'El auto debe devolverse limpio y con el tanque lleno.'}<br>
    ${t.faq_cargo_limpieza || 'Fumar dentro del vehículo o entregarlo con suciedad excesiva genera un cargo de'} <strong>$4,000 MXN</strong>.`
  },

  {
    q: ['modificar','cancelar','cambiar','reprogramar','reembolso'],
    cat: 'reservas',
    a: `<strong>📝 ${t.faq_modificaciones_titulo || 'Modificaciones y cancelaciones'}</strong><br><br>
    ${t.faq_modificar_desde || 'Puedes modificar o cancelar tu reserva desde'} <strong>"${t.faq_mi_reserva || 'Mi reserva'}"</strong>.<br><br>
    ${t.faq_cancelaciones_sujetas || 'Las cancelaciones están sujetas a la política vigente según la anticipación.'}<br><br>
    ${t.faq_seguro_cancelacion || 'También puedes contratar un seguro de cancelación para obtener un reembolso total.'}`
  },

  {
    q: ['edad','25','jóvenes','menor','años','requisitos edad','cuantos años'],
    cat: 'requisitos',
    a: `<strong>🎂 ${t.faq_edad_titulo || 'Requisitos de edad'}</strong><br><br>
    ${t.faq_edad_estandar || 'Edad estándar:'} <strong>25 ${t.faq_anos || 'años'}</strong>.<br>
    ${t.faq_edad_minima || 'Edad mínima permitida:'} <strong>21 ${t.faq_anos || 'años'}</strong>.<br><br>
    ⚠️ ${t.faq_conductor_joven || 'De 21 a 24 años aplica cargo de conductor joven y coberturas adicionales.'}`
  },

  {
    q: ['reservar','reservación','reservaciones','rentar','alquilar'],
    cat: 'reservas',
    a: `<strong>🚗 ${t.faq_iniciar_reservacion_titulo || 'Iniciar reservación'}</strong><br><br>
    👉 <a href="/reservaciones" target="_blank"><strong>${t.faq_iniciar_reservacion || 'Iniciar reservación'}</strong></a><br><br>
    💬 ${t.faq_ayuda_personalizada || 'Si necesitas ayuda personalizada:'}<br>
    <a href="https://wa.me/${waNumber}" target="_blank"><strong>${t.whatsapp || 'WhatsApp'}</strong></a>`
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
     else { bubble(t.faq_bienvenida || '¡Hola! Soy tu asistente de Viajero. Pregúntame sobre <strong>reservas, pagos, requisitos, seguros</strong> o <strong>entrega/devolución</strong>. También puedes usar los atajos de abajo 👇'); }
    }
    function clearHistory(){
      localStorage.removeItem(STORAGE_KEY);
      chatBody.innerHTML='';
      bubble(t.faq_bienvenida_2 || '¡Hola! 👋 Soy tu asistente de <strong>Viajero Car Rental</strong>.\nPuedo ayudarte con <strong>reservas, pagos, requisitos, seguros</strong> y <strong>entrega/devolución</strong>.\n\n📅 ¿Quieres reservar ahora?\n👉 <a href="/reservaciones">Iniciar reservación</a>\n\nUsa los botones de abajo 👇');

    }

    const btnClear = qs('#clearChat');
    if (btnClear) btnClear.addEventListener('click', clearHistory);

    function findAnswer(text, catHint=null){
      const t = (text||'').toLowerCase();
      const items = catHint ? KB.filter(k=>k.cat===catHint) : KB;
      for(const item of items){ if(item.q.some(k => t.includes(k))) return item.a; }
      return window.faqTranslations?.faq_no_encontre || 'No encontré una respuesta exacta 🤔. ¿Puedes darme más detalles?\nSi prefieres, puedo conectarte con un agente humano.';
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
        bubble(`<i class="fa-solid fa-tag"></i> ${t.faq_quiero_saber || 'Quiero saber sobre'} <strong>${btn.textContent.trim()}</strong>.`,'user');
        setTyping(true); await wait(400);
        const example = KB.find(k=>k.cat===cat);
        setTyping(false); bubble(example ? example.a : (t.faq_categoria_sin_respuesta || 'Cuéntame qué necesitas de esta categoría.'),'bot'); saveHistory();
      });
    });

    // Agente humano (si existe boton)
    const btnAgent = qs('#btnAgent');
    if (btnAgent){
      btnAgent.addEventListener('click', ()=>{
        bubble(t.faq_agente_mensaje || 'Quiero hablar con un agente.','user');
         bubble((t.faq_agente_respuesta || 'Con gusto 🙌. Puedes escribirnos a WhatsApp o llamarnos:\n• WhatsApp: <a href="https://wa.me/{numero}" target="_blank" rel="noopener">+52 442 716 9793</a>\n• Teléfono: <a href="tel:+524427169793">+52 442 716 9793</a>').replace('{numero}', waNumber), 'bot');
        saveHistory();
      });
    }

    loadHistory();
  });
})();
