(function () {
  const onReady = (fn) =>
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', fn, { once: true })
      : fn();

  // ====== AUTH (icono) ======
  (function(){
    const AUTH_KEY = 'vj_auth';
    const URLS = { LOGIN:'login.html', PROFILE:'perfil.html' };
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
    const waNumber = '524421234567';
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
      { q:['documentos','requisitos','licencia','identificación','tarjeta'], cat:'requisitos',
        a:`Para rentar necesitas:
• Identificación oficial vigente (INE o Pasaporte).
• Licencia de conducir vigente (mínimo 1 año).
• Tarjeta de crédito para depósito en garantía a nombre del conductor.` },
      { q:['depósito','garantía','bloqueo','retención'], cat:'pagos',
        a:`El depósito en garantía depende de la categoría:
• Compacto/Intermedio: $5,000–$10,000 MXN
• SUV/Lujo: $12,000–$25,000 MXN
Se libera por tu banco de 3 a 10 días hábiles tras la devolución.` },
      { q:['efectivo','oxxo','mercado pago','paypal'], cat:'pagos',
        a:`Puedes pagar en efectivo, OXXO, Mercado Pago o PayPal.
El depósito en garantía **sí** debe ser con tarjeta de crédito.` },
      { q:['seguro','cobertura','daños','responsabilidad'], cat:'seguros',
        a:`Incluimos **Responsabilidad Civil (LI)**. Extras disponibles:
• SLI (a terceros), LDW (daños al auto), PAI (accidentes personales).` },
      { q:['horario','entregar','devolver','aeropuerto','pick up','drop off'], cat:'entrega',
        a:`Horario Lun–Dom 8:00–22:00 h. Pick-up/Drop-off en Central Park Qro., Aeropuerto QRO y Aeropuerto del Bajío (León).` },
      { q:['modificar','cancelar','cambiar','reprogramar','reembolso'], cat:'reservas',
        a:`Modifica o cancela en "Mi reserva". Tarifas flexibles sin penalización con 24 h de anticipación. Promo puede no ser reembolsable.` },
      { q:['edad','25','jóvenes','menor'], cat:'requisitos',
        a:`Edad mínima **25 años**. De 21–24 años aplica cargo de conductor joven y coberturas adicionales.` },
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
      bubble(`¡Hola! Soy tu asistente de Viajero. Pregúntame lo que necesites 👋`);
    }

    const btnClear = qs('#btnClear');
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
    qsa('.sg').forEach(b=> b.addEventListener('click', ()=>{
      input.value = b.dataset.q || b.textContent.trim();
      form.dispatchEvent(new Event('submit'));
    }));

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
• WhatsApp: <a href="https://wa.me/${waNumber}" target="_blank" rel="noopener">+52 442 123 4567</a>
• Teléfono: <a href="tel:+524421234567">+52 442 123 4567</a>`, 'bot');
        saveHistory();
      });
    }

    loadHistory();
  });
})();
