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

  const topbar = document.querySelector('.topbar');
  function toggleTopbar(){ (window.scrollY>40)? topbar.classList.add('solid') : topbar.classList.remove('solid'); }
  toggleTopbar(); window.addEventListener('scroll', toggleTopbar, {passive:true});

  document.querySelector('.hamburger')?.addEventListener('click',()=>{
    const menu=document.querySelector('.menu'); const visible=getComputedStyle(menu).display!=='none';
    menu.style.display=visible?'none':'flex'; if(!visible){ menu.style.flexDirection='column'; menu.style.gap='12px'; }
  });

  (function markActive(){
    const current = (location.pathname.split('/').pop() || 'inicio.html').toLowerCase();
    document.querySelectorAll('.menu a').forEach(a=>{
      const href=(a.getAttribute('href')||'').toLowerCase();
      a.classList.toggle('active', href===current);
    });
  })();

  const startInput = document.getElementById('date-start');
  const endInput   = document.getElementById('date-end');
  let anchor = null;
  let startDate = null, endDate=null;
  let view = new Date();

  const binds = document.querySelectorAll('.nice-date');
  binds.forEach(b=>{
    const pop = b.querySelector('.cal-pop');
    b.addEventListener('click', (e)=>{
      closeAllPops();
      anchor = b.dataset.bind;
      renderCalendar(pop);
      pop.classList.add('show');
      e.stopPropagation();
    });
  });
  document.addEventListener('click', closeAllPops);
  function closeAllPops(){ document.querySelectorAll('.cal-pop').forEach(p=>p.classList.remove('show')); }

  function fmt(d){ const dd=String(d.getDate()).padStart(2,'0'); const mm=String(d.getMonth()+1).padStart(2,'0'); const yy=d.getFullYear(); return `${dd}/${mm}/${yy}`; }
  function sameDay(a,b){ return a && b && a.getFullYear()==b.getFullYear() && a.getMonth()==b.getMonth() && a.getDate()==b.getDate(); }
  function inRange(d){ if(!startDate||!endDate) return false; const t=d.setHours(0,0,0,0); return t>startDate.setHours(0,0,0,0) && t<endDate.setHours(0,0,0,0); }

  function renderCalendar(container){
    const y=view.getFullYear(), m=view.getMonth();
    const first = new Date(y,m,1);
    const startGrid = new Date(y,m,1 - ((first.getDay()+6)%7)); // semana inicia Lunes

    container.innerHTML = `
      <div class="cal-head">
        <button type="button" aria-label="Mes anterior" data-nav="-1"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="month">${first.toLocaleString('es-MX',{month:'long',year:'numeric'})}</div>
        <button type="button" aria-label="Mes siguiente" data-nav="1"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="cal-grid">
        ${['L','M','X','J','V','S','D'].map(d=>`<div class="dow">${d}</div>`).join('')}
        ${Array.from({length:42}).map((_,i)=>{
          const d = new Date(startGrid); d.setDate(startGrid.getDate()+i);
          const isMuted = d.getMonth()!==m;
          const classes = [
            'day',
            isMuted?'muted':'',
            startDate && sameDay(d,new Date(startDate)) ? 'start':'',
            endDate   && sameDay(d,new Date(endDate))   ? 'end':'',
            inRange(new Date(d)) ? 'in-range':''
          ].join(' ');
          return `<div class="${classes}" data-date="${d.toISOString()}">${d.getDate()}</div>`;
        }).join('')}
      </div>
    `;

    container.querySelectorAll('[data-nav]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        view.setMonth(view.getMonth()+Number(btn.dataset.nav));
        renderCalendar(container);
      });
    });

    container.querySelectorAll('.day').forEach(cell=>{
      cell.addEventListener('click', ()=>{
        const d = new Date(cell.dataset.date);

        if(!startDate || (startDate && endDate)){ startDate = d; endDate = null; }
        else if(d < startDate){ startDate = d; endDate = null; }
        else { endDate = d; }

        if(startDate){ startInput.value = fmt(startDate); }
        if(endDate){ endInput.value = fmt(endDate); }

        if(endDate){ closeAllPops(); }
        else if(anchor==='start'){
          const endPop = document.querySelector('.nice-date[data-bind="end"] .cal-pop');
          closeAllPops(); endPop.classList.add('show');
        }

        renderCalendar(container);
      });
    });
  }

  const cards = [...document.querySelectorAll('.car')];
  function applyFilters(){
    const t   = (document.getElementById('f-type')?.value || 'all');
    const loc = (document.getElementById('f-location')?.value || 'all');

    cards.forEach(card=>{
      const okT  = (t==='all')  || (card.dataset.type===t);
      const okL  = (loc==='all')|| (card.dataset.location===loc);
      card.style.display = (okT && okL) ? '' : 'none';
    });
  }
  document.getElementById('btn-filter')?.addEventListener('click', applyFilters);
  ['f-type','f-location'].forEach(id => document.getElementById(id)?.addEventListener('change', applyFilters));
  applyFilters();

  document.querySelectorAll('.car-cta a').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      const card = btn.closest('.car');
      const carTitle = card.querySelector('h3')?.innerText.replace(/\s+o similar/i,'') || 'Auto';
      const params = new URLSearchParams();
      const startV = document.getElementById('date-start')?.value || '';
      const endV   = document.getElementById('date-end')?.value || '';
      const locV   = document.getElementById('f-location')?.value || 'all';
      const flight = (document.getElementById('f-flight')?.value || '').trim();

      if(startV) params.set('start', startV);
      if(endV)   params.set('end', endV);
      if(locV && locV!=='all') params.set('loc', locV);
      if(flight) params.set('flight', flight);
      params.set('type', card.dataset.type || '');
      params.set('trans', card.dataset.trans || '');
      params.set('title', carTitle);

      location.href = 'reserva.html?' + params.toString();
    });
  });

  const y = document.getElementById('year'); if (y) y.textContent = new Date().getFullYear();
