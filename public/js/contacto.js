(function(){
    const AUTH_KEY = 'vj_auth';
    const URLS = { LOGIN:'login.html', PROFILE:'perfil.html' };

    if(!window.VJ_AUTH){
      function getAuth(){ try{ return JSON.parse(localStorage.getItem(AUTH_KEY)||'null'); }catch(e){ return null; } }
      function isLogged(){ return !!localStorage.getItem(AUTH_KEY); }
      window.VJ_AUTH = { getAuth, isLogged, URLS };
    }

    function syncAccountIcon(){
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
    }
    document.addEventListener('DOMContentLoaded', syncAccountIcon);
    window.addEventListener('storage', e=>{ if(e.key===AUTH_KEY) syncAccountIcon(); });
  })();

  const qs = s=>document.querySelector(s);
  const qsa = s=>[...document.querySelectorAll(s)];

  const topbar = qs('.topbar');
  function toggleTopbar(){ window.scrollY>40 ? topbar.classList.add('solid') : topbar.classList.remove('solid'); }
  toggleTopbar(); window.addEventListener('scroll', toggleTopbar, {passive:true});

  qs('.hamburger')?.addEventListener('click', ()=>{
    const m=qs('.menu'); const show=getComputedStyle(m).display==='none';
    m.style.display=show?'flex':'none';
    if(show){m.style.flexDirection='column';m.style.gap='12px';}
  });

  (function markActive(){
    const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
    qsa('.menu a').forEach(a=>{
      const href=(a.getAttribute('href')||'').toLowerCase();
      a.classList.toggle('active', href===current);
    });
  })();

  qs('#year').textContent = new Date().getFullYear();

  const waNumber = '524425574508';
  function buildWA(text){ return `https://wa.me/${waNumber}?text=${encodeURIComponent(text)}`; }

  function waFromForm(){
    const name = qs('#fName')?.value?.trim() || '';
    const phone = qs('#fPhone')?.value?.trim() || '';
    const email = qs('#fEmail')?.value?.trim() || '';
    const subject = qs('#fSubject')?.value?.trim() || 'Contacto web';
    const message = qs('#fMessage')?.value?.trim() || 'Hola Viajero, me interesa información sobre la renta de autos.';
    const lines = [
      `Hola Viajero 👋`,
      `Asunto: ${subject}`,
      `Mensaje: ${message}`,
    ];
    if(name)  lines.push(`Nombre: ${name}`);
    if(phone) lines.push(`Tel: ${phone}`);
    if(email) lines.push(`Email: ${email}`);
    return lines.join('\n');
  }

  qs('#btnWhatsapp').addEventListener('click', ()=>{
    window.open(buildWA(waFromForm()), '_blank', 'noopener');
  });
  qs('#ctaWhats').addEventListener('click', ()=>{
    const t = '¡Hola! Necesito ayuda con una cotización.';
    window.open(buildWA(t), '_blank', 'noopener');
  });

  const messageEl = qs('#fMessage'), counterEl = qs('#charCount');
  function syncCount(){ counterEl.textContent = (messageEl.value||'').length; }
  messageEl.addEventListener('input', syncCount); syncCount();

  (function prefillFromAuth(){
    const u = window.VJ_AUTH?.getAuth?.();
    if(!u) return;
    if(u.name && !qs('#fName').value)  qs('#fName').value  = u.name;
    if(u.email && !qs('#fEmail').value) qs('#fEmail').value = u.email;
    if(u.phone && !qs('#fPhone').value) qs('#fPhone').value = u.phone;
  })();

  function showToast(msg='¡Mensaje enviado! Te contactaremos muy pronto.'){
    const toast = qs('#toast');
    toast.innerHTML = `<i class="fa-solid fa-check"></i> ${msg}`;
    toast.classList.add('show');
    setTimeout(()=> toast.classList.remove('show'), 3200);
  }

  function formToJSON(form){
    return Object.fromEntries(new FormData(form).entries());
  }

  qs('#contactForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const form = e.target;

    if((form.company||{}).value){ return; }

    const requiredIds = ['fName','fPhone','fEmail','fMessage'];
    let ok = true;
    requiredIds.forEach(id=>{
      const el = qs('#'+id);
      const valid = el.checkValidity();
      el.style.boxShadow = valid ? '' : '0 0 0 4px rgba(178,34,34,.12)';
      el.style.borderColor = valid ? '' : 'var(--brand)';
      ok = ok && valid;
    });
    if(!ok){ el = requiredIds.find(id=>!qs('#'+id).checkValidity()); qs('#'+el).focus(); return; }

    const data = formToJSON(form);
    data.promo = qs('#promo').checked ? 1 : 0;
    data.source = 'contacto_web';

    showToast();

    form.reset(); syncCount();
  });
