// ====== AUTH SNIPPET (icono dinámico y helpers globales) ======
  (function(){
    const AUTH_KEY = 'vj_auth';
    const URLS = { HOME:'inicio.html', LOGIN:'login.html', PROFILE:'perfil.html' };

    function getAuth(){
      try{ return JSON.parse(localStorage.getItem(AUTH_KEY)||'null'); }catch(e){ return null; }
    }
    function isLogged(){ return !!localStorage.getItem(AUTH_KEY); }
    function setAuth(user){ localStorage.setItem(AUTH_KEY, JSON.stringify(user)); syncAccountIcon(); }
    function clearAuth(){ localStorage.removeItem(AUTH_KEY); syncAccountIcon(); }

    function syncAccountIcon(){
      const link = document.getElementById('accountLink');
      if(!link) return;
      if(isLogged()){
        const u = getAuth()||{};
        link.href = URLS.PROFILE;
        link.title = 'Mi perfil';
        link.innerHTML = `<span class="avatar-mini">${(u.name?.[0] || u.email?.[0] || 'U').toUpperCase()}</span>`;
      }else{
        link.href = URLS.LOGIN;
        link.title = 'Iniciar sesión';
        link.innerHTML = '<i class="fa-regular fa-user"></i>';
      }
    }

    window.VJ_AUTH = { getAuth, isLogged, setAuth, clearAuth, URLS };

    document.addEventListener('DOMContentLoaded', syncAccountIcon);
    window.addEventListener('storage', (e)=>{ if(e.key===AUTH_KEY) syncAccountIcon(); });
  })();

  // Navbar: glass -> sólida + hamburguesa
  (function(){
    const topbar = document.querySelector('.topbar');
    function onScroll(){ if(window.scrollY>40) topbar.classList.add('solid'); else topbar.classList.remove('solid'); }
    onScroll(); window.addEventListener('scroll', onScroll, {passive:true});
    const btn=document.querySelector('.hamburger'), menu=document.querySelector('.menu');
    btn?.addEventListener('click', ()=>{ const vis=getComputedStyle(menu).display!=='none'; menu.style.display=vis?'none':'flex'; if(!vis){ menu.style.flexDirection='column'; menu.style.gap='14px'; }});
  })();

  // Carrusel principal
  (function(){
    const slides=[...document.querySelectorAll('.slide')]; if(!slides.length) return;
    let i=0; const show=x=>slides.forEach((s,k)=>s.classList.toggle('active',k===x));
    setInterval(()=>{ i=(i+1)%slides.length; show(i); },5000); show(i);
  })();

  // Carruseles de secciones
  (function(){
    document.querySelectorAll('.media-carousel').forEach((wrap,n)=>{
      const items=[...wrap.querySelectorAll('.media-slide')]; if(items.length<=1) return;
      const interval=Number(wrap.dataset.interval||5000); let i=0;
      const show=x=>items.forEach((el,k)=>el.classList.toggle('active',k===x));
      setInterval(()=>{ i=(i+1)%items.length; show(i); }, interval + (n*300)); show(i);
    });
  })();

  // Año footer
  (function(){ const y=document.getElementById('year'); if(y) y.textContent=new Date().getFullYear(); })();

  // ===== Modal Bienvenida (se muestra si vienes del login con banderas en sessionStorage) =====
  (function(){
    const modal = document.getElementById('welcomeModal');
    const nameEl = document.getElementById('wmName');
    const closeBtn = document.getElementById('wmClose');
    const okBtn = document.getElementById('wmOk');

    function open(){ modal?.classList.add('show'); }
    function close(){ modal?.classList.remove('show'); }

    closeBtn?.addEventListener('click', close);
    modal?.querySelector('.modal-backdrop')?.addEventListener('click', close);
    okBtn?.addEventListener('click', close);

    // Checa banderas que setea login antes de redirigir
    const should = sessionStorage.getItem('vj_welcome_home') === '1';
    const who = sessionStorage.getItem('vj_welcome_name') || (window.VJ_AUTH?.getAuth()?.name) || 'Viajero';

    if(should){
      if(nameEl) nameEl.textContent = who;
      open();
      // limpia para que no vuelva a mostrarse
      sessionStorage.removeItem('vj_welcome_home');
      sessionStorage.removeItem('vj_welcome_name');
    }
  })();

  // ===== pickupPlace, dropoffPlace y validación de fechas/hora (con Flatpickr + rangePlugin) =====
  (function(){
    // ---- Lugares: solo Querétaro y Guanajuato ----
    const locationsByRegion = {
      "Querétaro": [
        "Querétaro Aeropuerto",
        "Querétaro Central de Autobuses",
        "Querétaro Central Park"
      ],
      "Guanajuato": [
        "León Aeropuerto",
        "León Central de Autobuses",
        "Guanajuato Centro"
      ]
    };

    const pickupPlace = document.getElementById('pickupPlace');
    const dropoffPlace = document.getElementById('dropoffPlace');
    const rentalForm = document.getElementById('rentalForm');
    const rangeSummary = document.getElementById('rangeSummary');

    if(!pickupPlace || !dropoffPlace || !rentalForm) return;

    // helper: smooth scroll centrado
    function smoothScroll(el){
      if(!el) return;
      try{
        el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
        return;
      }catch(e){}
      const rect = el.getBoundingClientRect();
      const absoluteY = window.pageYOffset + rect.top;
      window.scrollTo({ top: absoluteY - (window.innerHeight/2) + (rect.height/2), behavior: 'smooth' });
    }

    // Poblador que crea optgroups (mantiene un placeholder al principio)
    function populateCitySelect(selectEl){
      selectEl.innerHTML = '';

      // placeholder al inicio
      const placeholderOpt = document.createElement('option');
      placeholderOpt.value = '';
      placeholderOpt.textContent = '-- Selecciona ciudad --';
      placeholderOpt.disabled = true;
      placeholderOpt.selected = true;
      selectEl.appendChild(placeholderOpt);

      // recorrer regiones/estados y crear optgroups
      Object.keys(locationsByRegion).forEach(region => {
        const group = document.createElement('optgroup');
        group.label = region;

        locationsByRegion[region].forEach(loc => {
          const opt = document.createElement('option');
          opt.value = loc;
          opt.textContent = loc;
          group.appendChild(opt);
        });

        selectEl.appendChild(group);
      });

      selectEl.setAttribute('aria-live','polite');
    }

    // intenta seleccionar en un select el valor dado; devuelve true si lo encontró
    function findAndSelect(selectEl, value){
      if(!value) return false;
      for(const opt of selectEl.options){
        if(opt.value === value){
          selectEl.value = value;
          // disparar evento change para que otros listeners reaccionen
          const ev = new Event('change', { bubbles: true });
          selectEl.dispatchEvent(ev);
          return true;
        }
      }
      return false;
    }

    // inicializar ambos selects
    populateCitySelect(pickupPlace);
    populateCitySelect(dropoffPlace);

    // Cuando el usuario selecciona pickup, intentamos copiarlo a dropoff si existe
    pickupPlace.addEventListener('change', (e)=>{
      const chosen = e.target.value;
      // intenta seleccionar el mismo en dropoff; si no existe, no pasa nada
      findAndSelect(dropoffPlace, chosen);
    });

    // Inicializar Flatpickr en inputs de fecha y hora
    // Aseguramos locale 'es' (archivo de idioma ya cargado)
    if(window.flatpickr){
      // Inicializa el picker de rango: abre desde #pickupDate y escribe también en #dropoffDate
      const rangePicker = flatpickr("#pickupDate", {
        locale: 'es',
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        // Vincula el segundo input al rango
        plugins: [ new rangePlugin({ input: "#dropoffDate" }) ],
        onChange: updateSummary,
      });

      // Hacemos que si el usuario enfoca el input #dropoffDate abramos el mismo calendario (UX)
      const dropInput = document.getElementById('dropoffDate');
      if(dropInput){
        dropInput.addEventListener('focus', function(e){
          try{ rangePicker.open(); }catch(err){}
        });
        dropInput.addEventListener('click', function(){ try{ rangePicker.open(); }catch(e){} });
      }

      // Inicializamos una instancia ligera para dropoffDate con altInput mostrado pero clickOpens false
      try {
        flatpickr("#dropoffDate", {
          allowInput: true,
          clickOpens: false,
          altInput: true,
          altFormat: "d/m/Y",
          dateFormat: "Y-m-d",
          minDate: "today",
          onChange: updateSummary
        });
      } catch(e){ /* no crítico */ }

      // horas: time-only, formato AM/PM
      flatpickr("#pickupTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 5,
        onChange: updateSummary
      });
      flatpickr("#dropoffTime", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 5,
        onChange: updateSummary
      });
    }

    // Función que convierte "h:mm AM/PM" o "HH:MM" en horas/minutos 24h
    function parseTimeTo24(timeStr){
      if(!timeStr) return { hh:0, mm:0 };
      timeStr = timeStr.trim();
      const ampmMatch = timeStr.match(/(am|pm|AM|PM)$/);
      if(ampmMatch){
        const timePart = timeStr.replace(/\s?(AM|PM|am|pm)$/,'');
        const ampm = ampmMatch[0].toLowerCase();
        const [hRaw, mRaw] = timePart.split(':').map(v => Number(v || 0));
        let hh = Number(hRaw || 0);
        const mm = Number(mRaw || 0);
        if(ampm === 'am'){
          if(hh === 12) hh = 0;
        } else {
          if(hh !== 12) hh = hh + 12;
        }
        return { hh, mm };
      } else {
        const [hh, mm] = (timeStr.split(':').map(v => Number(v || 0)));
        return { hh: Number(hh||0), mm: Number(mm||0) };
      }
    }

    // Función que crea Date a partir de date + time inputs (si falta hora, se asume 00:00)
    function buildDateTime(dateInputId, timeInputId){
      const dateEl = document.getElementById(dateInputId);
      const timeEl = document.getElementById(timeInputId);
      if(!dateEl) return null;
      const dateVal = dateEl.value; // "YYYY-MM-DD"
      if(!dateVal) return null;
      const timeVal = (timeEl?.value || '00:00').trim(); // puede ser "2:30 PM"
      const [y,m,d] = dateVal.split('-').map(Number);
      const { hh, mm } = parseTimeTo24(timeVal);
      return new Date(y, m-1, d, hh, mm, 0, 0);
    }

    // Update del resumen
    function updateSummary(){
      const s = buildDateTime('pickupDate','pickupTime');
      const e = buildDateTime('dropoffDate','dropoffTime');
      if(!s || !e){ rangeSummary.textContent = ''; return; }
      if(e < s){ rangeSummary.textContent = 'La fecha/hora de devolución no puede ser anterior a la de entrega.'; return; }
      const ms = e - s;
      const h = Math.round(ms/36e5);
      const d = Math.ceil(h/24);
      rangeSummary.textContent = `Renta por ${d} día(s) · ~${h} hora(s)`;
    }

    // Escuchar cambios en inputs (por si el navegador modifica manualmente)
    ['pickupDate','pickupTime','dropoffDate','dropoffTime'].forEach(id=>{
      const el = document.getElementById(id);
      el?.addEventListener('change', updateSummary);
      el?.addEventListener('input', updateSummary);
    });

    // Submit (validación simple + redirección a reserva.html)
    rentalForm.addEventListener('submit', function(e){
      e.preventDefault();

      const place = pickupPlace.value;
      const dropPlace = dropoffPlace.value;
      const pickupDt = buildDateTime('pickupDate','pickupTime');
      const dropoffDt = buildDateTime('dropoffDate','dropoffTime');
      const carType = document.getElementById('carType')?.value || null;

      if(!place){ alert('Selecciona un lugar de renta.'); pickupPlace.focus(); smoothScroll(pickupPlace); return; }
      if(!dropPlace){ alert('Selecciona un lugar de devolución.'); dropoffPlace.focus(); smoothScroll(dropoffPlace); return; }

      if(!pickupDt){ alert('Selecciona la fecha y hora de entrega.'); document.getElementById('pickupDate')?.focus(); smoothScroll(document.getElementById('pickupDate')); return; }
      if(!dropoffDt){ alert('Selecciona la fecha y hora de devolución.'); document.getElementById('dropoffDate')?.focus(); smoothScroll(document.getElementById('dropoffDate')); return; }
      if(dropoffDt < pickupDt){ alert('La fecha/hora de devolución no puede ser anterior a la de entrega.'); document.getElementById('dropoffDate')?.focus(); smoothScroll(document.getElementById('dropoffDate')); return; }

      // Construir resumen (sin oficinas ni edad)
      const summary = {
        pickupPlace: place,
        dropoffPlace: dropPlace,
        pickupDate: document.getElementById('pickupDate')?.value || null,
        pickupTime: document.getElementById('pickupTime')?.value || null,
        dropoffDate: document.getElementById('dropoffDate')?.value || null,
        dropoffTime: document.getElementById('dropoffTime')?.value || null,
        carType: carType
      };

      try { sessionStorage.setItem('vj_search_summary', JSON.stringify(summary)); } catch(e){ /* noop */ }

      // redirigir a la vista de reservas
      window.location.href = 'reserva.html';
    });
  })();
