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
  const money = n=>`$${Number(n).toLocaleString('es-MX')} MXN`;
  const numFromMoney = (txt) => Number(String(txt).replace(/[^\d.]/g,'') || 0);
  const fmtMoney = (n) => `$${n.toLocaleString('es-MX')} MXN`;
  const fmtDT = (d) => d ? d.toLocaleString('es-MX',{weekday:'short',day:'2-digit',month:'short',hour:'numeric',minute:'2-digit',hour12:true}).replace(/\.$/,'') : '—';

  const PREF_DISCOUNT = 0.10;
  const getPrefPrice = (base) => Math.max(0, Math.round(base*(1 - PREF_DISCOUNT)));

  const topbar = qs('.topbar');
  function toggleTopbar(){ window.scrollY>40 ? topbar.classList.add('solid') : topbar.classList.remove('solid'); }
  toggleTopbar(); window.addEventListener('scroll', toggleTopbar, {passive:true});
  qs('.hamburger')?.addEventListener('click', ()=>{
    const m=qs('.menu'); const show=getComputedStyle(m).display==='none';
    m.style.display=show?'flex':'none'; if(show){m.style.flexDirection='column';m.style.gap='12px';}
  });

  function setActiveStep(n){
    qsa('.step-item').forEach(el=>{
      el.classList.toggle('active', Number(el.dataset.step)===n);
      el.classList.toggle('done', Number(el.dataset.step)<n);
    });
    const total=qsa('.step-item').length;
    const pct=(n-1)/(total-1)*100;
    qs('#progressFill').style.width=`${pct}%`;
  }
  setActiveStep(2);

  const state = { car:null, addons:{}, days:12, taxRate:.16, payment:null, loc:'Querétaro Aeropuerto', isPreferred:false,
  memberId: null };

  let startPicker; let userPicker; let dobPicker;

  function localizeES(){ if(window.flatpickr?.l10ns?.es) flatpickr.localize(flatpickr.l10ns.es); }

  function initEditPickers(defS, defE){
    localizeES();
    startPicker = flatpickr('#start', {
      enableTime:true, time_24hr:false, minuteIncrement:5,
      altInput:true, altFormat:'d/m/Y h:i K', dateFormat:'Z',
      defaultDate:[defS, defE],
      plugins:[ new rangePlugin({ input:'#end' }) ],
      onChange:(dates)=>{ if(dates.length===2) applyDates(dates[0], dates[1]); }
    });
  }

  let ufDateStartPicker, ufTimeStartPicker, ufDateEndPicker, ufTimeEndPicker;

  function initUserPickers(defaultStart, defaultEnd){
    localizeES();

    ufDateStartPicker = flatpickr('#ufStartDate', {
      altInput:true, altFormat:'d/m/Y', dateFormat:'Y-m-d',
      defaultDate: defaultStart
    });
    ufDateEndPicker = flatpickr('#ufEndDate', {
      altInput:true, altFormat:'d/m/Y', dateFormat:'Y-m-d',
      defaultDate: defaultEnd
    });

    ufTimeStartPicker = flatpickr('#ufStartTime', {
      enableTime:true, noCalendar:true, minuteIncrement:5,
      time_24hr:false, altInput:true, altFormat:'h:i K', dateFormat:'H:i',
      defaultDate: defaultStart
    });
    ufTimeEndPicker = flatpickr('#ufEndTime', {
      enableTime:true, noCalendar:true, minuteIncrement:5,
      time_24hr:false, altInput:true, altFormat:'h:i K', dateFormat:'H:i',
      defaultDate: defaultEnd
    });

    dobPicker = flatpickr('#dob', {
      altInput:true, altFormat:'d/m/Y', dateFormat:'Y-m-d',
      maxDate: new Date(),
    });

    const recompute = ()=>{
      const sDate = ufDateStartPicker?.selectedDates?.[0] || defaultStart;
      const eDate = ufDateEndPicker?.selectedDates?.[0]   || defaultEnd;
      const sTime = ufTimeStartPicker?.selectedDates?.[0] || defaultStart;
      const eTime = ufTimeEndPicker?.selectedDates?.[0]   || defaultEnd;

      const s = new Date(
        sDate.getFullYear(), sDate.getMonth(), sDate.getDate(),
        sTime.getHours(), sTime.getMinutes(), 0, 0
      );
      const e = new Date(
        eDate.getFullYear(), eDate.getMonth(), eDate.getDate(),
        eTime.getHours(), eTime.getMinutes(), 0, 0
      );

      applyDates(s, e);
    };

    [ufDateStartPicker, ufDateEndPicker, ufTimeStartPicker, ufTimeEndPicker].forEach(fp=>{
      fp?.config && (fp.config.onChange = [...(fp.config.onChange||[]), recompute]);
    });

    recompute();
  }

  function setUserPickersFromDates(s, e){
    ufDateStartPicker?.setDate(s, true);
    ufTimeStartPicker?.setDate(s, true);
    ufDateEndPicker?.setDate(e, true);
    ufTimeEndPicker?.setDate(e, true);
  }

  function applyDates(s,e){
    if(!(s && e)) return;
    qs('#briefStart').textContent = fmtDT(s);
    qs('#briefEnd').textContent   = fmtDT(e);
    qs('#sumStart').textContent   = `${fmtDT(s)} · ${state.loc}`;
    qs('#sumEnd').textContent     = `${fmtDT(e)} · ${state.loc}`;
    state.days = Math.max(1, Math.ceil((e - s)/86400000));

    if(startPicker){
      const a=startPicker.selectedDates||[];
      if(!(a[0] && a[1] && +a[0]===+s && +a[1]===+e)) startPicker.setDate([s,e], true);
    }
    if(ufDateStartPicker && ufTimeStartPicker && ufDateEndPicker && ufTimeEndPicker){
      const s0 = ufDateStartPicker.selectedDates?.[0], t0 = ufTimeStartPicker.selectedDates?.[0];
      const e0 = ufDateEndPicker.selectedDates?.[0],   t1 = ufTimeEndPicker.selectedDates?.[0];
      const needSync =
        !s0 || !t0 || !e0 || !t1 ||
        s0.toDateString() !== s.toDateString() ||
        t0.getHours() !== s.getHours() || t0.getMinutes() !== s.getMinutes() ||
        e0.toDateString() !== e.toDateString() ||
        t1.getHours() !== e.getHours() || t1.getMinutes() !== e.getMinutes();
      if(needSync) setUserPickersFromDates(s, e);
    }
  }

  function toggleFlightField(){
    const input = qs('#flight');
    if(!input) return;
    const isAirport = /aeropuerto/i.test(state.loc || '');
    input.disabled = !isAirport;
    input.placeholder = isAirport ? 'Ej. AM1234' : 'Disponible solo si recoges en Aeropuerto';
    const wrap = input.closest('.field');
    if(wrap) wrap.style.opacity = isAirport ? '1' : '.6';
  }

  const initialStart = new Date(); initialStart.setHours(12,0,0,0);
  const initialEnd   = new Date(initialStart.getTime()+11*86400000);
  qs('#briefLoc').textContent = state.loc;
  applyDates(initialStart, initialEnd);
  toggleFlightField();

  const panel=qs('#editPanel');
  qs('#toggleEdit').addEventListener('click',()=>{
    panel.style.display = panel.style.display ? '' : 'block';
    if(!startPicker) initEditPickers(initialStart, initialEnd);
  });
  qs('#cancelEdit').addEventListener('click',()=>panel.style.display='none');

  qs('#editPanel').addEventListener('submit',e=>{
    e.preventDefault();
    state.loc = qs('#loc').value;
    const [s,e2] = startPicker?.selectedDates || [];
    applyDates(s,e2);
    toggleFlightField();
    panel.style.display='none';
  });

  qs('#backTo1')?.addEventListener('click', ()=>{
    panel.style.display='block';
    if(!startPicker) initEditPickers(initialStart, initialEnd);
    setActiveStep(1); window.scrollTo({top:0, behavior:'smooth'});
  });
  qs('#backTo2')?.addEventListener('click', ()=>{
    qs('#step3').classList.add('hidden');
    qs('#step2').classList.remove('hidden');
    setActiveStep(2); window.scrollTo({top:0, behavior:'smooth'});
  });
  qs('#backTo3')?.addEventListener('click', ()=>{
    qs('#step4').classList.add('hidden');
    qs('#step3').classList.remove('hidden');
    setActiveStep(3); window.scrollTo({top:0, behavior:'smooth'});
  });

  qs('#editDates')?.addEventListener('click', ()=>{
    panel.style.display='block';
    if(!startPicker) initEditPickers(initialStart, initialEnd);
    window.scrollTo({top:0, behavior:'smooth'});
  });
  qs('#editCar')?.addEventListener('click', ()=>{
    qs('#step4').classList.add('hidden');
    qs('#step2').classList.remove('hidden');
    setActiveStep(2); window.scrollTo({top:0, behavior:'smooth'});
  });

  function fillMemberOffers(){
    qsa('.r-card').forEach(card=>{
      const payCounter = Number(card.dataset.payCounter);
      const payPre     = Number(card.dataset.payPre);

      const mCounter = getPrefPrice(payCounter);
      const mPre     = getPrefPrice(payPre);

      const p1 = card.querySelector('.member-offer [data-member="mostrador"]');
      const s1 = card.querySelector('.member-offer [data-save="mostrador"]');
      if(p1){ p1.textContent = fmtMoney(mCounter); }
      if(s1){ s1.textContent = `Ahorra ${fmtMoney(payCounter - mCounter)}`; }

      const p2 = card.querySelector('.member-offer [data-member="prepago"]');
      const s2 = card.querySelector('.member-offer [data-save="prepago"]');
      if(p2){ p2.textContent = fmtMoney(mPre); }
      if(s2){ s2.textContent = `Ahorra ${fmtMoney(payPre - mPre)}`; }
    });
  }
  fillMemberOffers();

  function ensurePrefModal(){
    if(document.querySelector('#prefModal')) return;
    const wrap = document.createElement('div');
    wrap.className = 'modal-overlay';
    wrap.id = 'prefModal';
    wrap.style.display = 'none';
    wrap.innerHTML = `
      <div class="modal" role="dialog" aria-labelledby="prefTitle" aria-modal="true">
        <h3 id="prefTitle" style="margin:0 0 8px;font-weight:900">Ingresa tu ID de miembro preferente</h3>
        <p style="margin:0 0 8px;color:#6b7280;font-size:13px">Validamos tu membresía para aplicar el precio de socio.</p>
        <input id="prefInput" class="pref-input" placeholder="Ej. VJ-123456" />
        <div id="prefError" style="display:none;color:#b22222;font-size:12px;text-align:left;margin-top:-6px;">
          Ingresa un ID válido (5–20 caracteres alfanuméricos).
        </div>
        <div class="modal-actions">
          <button type="button" id="prefCancel" class="btn btn-gray">Cancelar</button>
          <button type="button" id="prefOk" class="btn btn-primary">Continuar</button>
        </div>
      </div>`;
    document.body.appendChild(wrap);

    wrap.addEventListener('click', (e)=>{ if(e.target===wrap) closePrefModal(); });
    wrap.querySelector('#prefCancel').addEventListener('click', closePrefModal);
  }
  function openPrefModal(onConfirm){
    ensurePrefModal();
    const overlay = document.querySelector('#prefModal');
    const input   = overlay.querySelector('#prefInput');
    const error   = overlay.querySelector('#prefError');
    error.style.display = 'none';
    input.value = state.memberId || '';
    overlay.style.display = 'flex';
    input.focus();

    const confirm = () => {
      const val = (input.value||'').trim();
      if(!/^[A-Z0-9-]{5,20}$/i.test(val)){
        error.style.display = 'block';
        input.focus();
        return;
      }
      state.memberId = val;
      closePrefModal();
      onConfirm && onConfirm();
    };
    overlay.querySelector('#prefOk').onclick = confirm;
    input.onkeydown = (ev)=>{ if(ev.key==='Enter') confirm(); };
  }
  function closePrefModal(){
    const overlay = document.querySelector('#prefModal');
    if(overlay) overlay.style.display = 'none';
  }

  qsa('.pick').forEach(btn=>{
    btn.addEventListener('click',()=> selectCarFromCard(btn,false));
  });

  qsa('.pick-member').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      if(state.memberId){
        selectCarFromCard(btn,true);
      }else{
        openPrefModal(()=> selectCarFromCard(btn,true));
      }
    });
  });

  function selectCarFromCard(btn, member=false){
    const card = btn.closest('.r-card');
    const plan = btn.dataset.plan;
    const baseRaw = Number(plan==='prepago' ? card.dataset.payPre : card.dataset.payCounter);
    const base = member ? getPrefPrice(baseRaw) : baseRaw;

    state.isPreferred = member;
    state.car = {
      name: card.dataset.name,
      img: card.querySelector('img').src,
      cat: card.dataset.cat,
      plan,
      baseRaw,
      base
    };
    qs('#sumName').textContent = state.car.name;
    qs('#sumCat').textContent  = `Categoría ${state.car.cat}`;
    qs('#sumImg').src = state.car.img;

    qs('#step2').classList.add('hidden');
    qs('#step3').classList.remove('hidden');
    setActiveStep(3);
    window.scrollTo({top:0, behavior:'smooth'});
  }

  qsa('.pick').forEach(btn=>{
    btn.addEventListener('click',()=> selectCarFromCard(btn,false));
  });
  qsa('.pick-member').forEach(btn=>{
    btn.addEventListener('click',()=> selectCarFromCard(btn,true));
  });

  qsa('.addon-card').forEach(card=>{
    const qtySpan=card.querySelector('.qty');
    const id=card.dataset.id;
    card.querySelector('.plus')?.addEventListener('click',()=>{
      const val=(state.addons[id]||0)+1; state.addons[id]=val; qtySpan.textContent=val;
    });
    card.querySelector('.minus')?.addEventListener('click',()=>{
      const val=Math.max(0,(state.addons[id]||0)-1);
      if(val===0) delete state.addons[id]; else state.addons[id]=val;
      qtySpan.textContent=val;
    });
  });

  function goStep4(){
    if(!state.car){ alert('Primero elige un auto.'); return; }

    const list = Object.entries(state.addons);
    if(list.length===0){ qs('#sumExtras').textContent='Sin complementos'; }
    else{
      qs('#sumExtras').innerHTML = list.map(([id,qty])=>{
        const card = qs(`.addon-card[data-id="${id}"]`);
        const name = card.dataset.name; const price = Number(card.dataset.price);
        return `<div>• ${name} × ${qty} — ${money(price*qty*state.days)}</div>`;
      }).join('');
    }
    const base = state.car?.base || 0;
    const addonsTotal = list.reduce((acc,[id,qty])=>{
      const price = Number(qs(`.addon-card[data-id="${id}"]`).dataset.price);
      return acc + price*qty*state.days;
    },0);
    const tax = Math.round((base+addonsTotal)*state.taxRate);
    const total = base + addonsTotal + tax;
    qs('#pBase').textContent = money(base);
    qs('#pAddons').textContent = money(addonsTotal);
    qs('#pTax').textContent = money(tax);
    qs('#pTotal').textContent = money(total);

    if(!ufDateStartPicker) initUserPickers(initialStart, initialEnd);
    setUserPickersFromDates(initialStart, initialEnd);
    toggleFlightField();

    qs('#step3').classList.add('hidden');
    qs('#step4').classList.remove('hidden');
    setActiveStep(4);
    window.scrollTo({top:0, behavior:'smooth'});
  }
  qs('#toStep4').addEventListener('click', goStep4);
  qs('#skipAddons').addEventListener('click', goStep4);

  qs('#reserveBtn').addEventListener('click', (e)=>{
    e.preventDefault();

    const total = numFromMoney(qs('#pTotal').textContent || 0);
    const col = qs('#userCol');
    col.innerHTML = `
      <div class="form-card">
        <h3>Pago</h3>

        <div class="pay-section">
          <div class="group-title">Método de pago</div>
          <div class="method-list" id="payMethods">
            <label class="method-pill is-active"><input type="radio" name="payMethod" value="tarjeta" checked><i class="fa-regular fa-credit-card"></i> Tarjeta</label>
            <label class="method-pill"><input type="radio" name="payMethod" value="paypal"><img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal"> PayPal</label>
            <label class="method-pill"><input type="radio" name="payMethod" value="oxxo"><img src="https://upload.wikimedia.org/wikipedia/commons/0/06/Oxxo_Logo.svg" alt="OXXO"> OXXO</label>
            <label class="method-pill"><input type="radio" name="payMethod" value="mp"><img src="https://upload.wikimedia.org/wikipedia/commons/1/16/MercadoPago.svg" alt="Mercado Pago"> Mercado Pago</label>
            <label class="method-pill"><input type="radio" name="payMethod" value="efectivo"><i class="fa-solid fa-money-bill-wave"></i> Efectivo (en mostrador)</label>
          </div>
        </div>

        <div class="pay-section">
          <div class="group-title">¿Cuánto deseas liquidar ahora?</div>
          <div class="plan-options" id="payPlan">
            <label class="plan-pill is-active"><input type="radio" name="plan" value="100" checked> 💯 100%</label>
            <label class="plan-pill"><input type="radio" name="plan" value="45"> 🔖 45% (anticipo)</label>
          </div>
          <div class="charge-box"><div class="charge-badge" id="chargeBadge">100% hoy</div><div class="charge-amount" id="chargeNow">${fmtMoney(total)}</div></div>
        </div>

        <form class="user-form" id="payForm">
          <div class="form-row card-field"><div class="field"><label>Nombre en la tarjeta</label><input placeholder="Como aparece en la tarjeta" required></div></div>
          <div class="form-row grid-2 card-field"><div class="field"><label>Número de tarjeta</label><input inputmode="numeric" placeholder="4111 1111 1111 1111" required></div><div class="field"><label>CVV</label><input inputmode="numeric" placeholder="123" required></div></div>
          <div class="form-row grid-2 card-field"><div class="field"><label>Mes/Año</label><input placeholder="MM/AA" required></div><div class="field"><label>Código postal</label><input required></div></div>
          <button class="btn btn-primary btn-block" id="payNow">Pagar ahora</button>
          <div class="pay-logos" style="margin-top:12px"><img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa"><img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard"><img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo_%282018%29.svg" alt="American Express"></div>
        </form>
      </div>`;

    const payState = { method:'tarjeta', planPct:100, total, get charge(){ return Math.round(this.total*(this.planPct/100)); } };
    const setActive = (listSel, el) => { qsa(`${listSel} label`).forEach(l=>l.classList.remove('is-active')); el.classList.add('is-active'); };
    const setPlanDisabled = (disabled) => {
      qsa('#payPlan .plan-pill').forEach(l=>l.classList.toggle('is-disabled', disabled));
      if(disabled){ payState.planPct = 0; }
      else if(payState.planPct===0){ payState.planPct = 100; setActive('#payPlan', qsa('#payPlan .plan-pill')[0]); }
    };
    const renderCharge = () => {
      if(payState.method==='efectivo'){ qs('#chargeBadge').textContent='En mostrador'; qs('#chargeNow').textContent=fmtMoney(0); }
      else{ qs('#chargeBadge').textContent=(payState.planPct===100)?'100% hoy':'45% hoy'; qs('#chargeNow').textContent=fmtMoney(payState.charge); }
    };
    const toggleCardFields = (show)=>{ qsa('#payForm .card-field').forEach(el=>el.style.display = show?'':'none'); qs('#payNow').textContent = show?'Pagar ahora':'Obtener número de reserva'; };

    qsa('#payMethods .method-pill').forEach(lbl=>{
      lbl.addEventListener('click', ()=>{
        const val = lbl.querySelector('input').value; payState.method=val; setActive('#payMethods', lbl);
        toggleCardFields(val==='tarjeta'); setPlanDisabled(val==='efectivo'); renderCharge();
      });
    });
    qsa('#payPlan .plan-pill').forEach(lbl=>{
      lbl.addEventListener('click', ()=>{ if(lbl.classList.contains('is-disabled')) return;
        payState.planPct = Number(lbl.querySelector('input').value); setActive('#payPlan', lbl); renderCharge();
      });
    });
    renderCharge(); toggleCardFields(true);

    qs('#payNow')?.addEventListener('click', ev=>{
      ev.preventDefault();
      const paidNow = (payState.method==='tarjeta') ? payState.charge : 0;
      state.payment = { method: payState.method, planPct: payState.planPct, paid: paidNow, total: payState.total };
      renderConfirmation();
    });
  });

  function refreshSelectedBaseAndTotalsIfVisible(){
    if(!state.car) return;
    const baseFrom = state.isPreferred ? getPrefPrice(state.car.baseRaw || state.car.base) : (state.car.baseRaw || state.car.base);
    state.car.base = baseFrom;

    const step4Visible = !qs('#step4').classList.contains('hidden');
    if(step4Visible){
      const list = Object.entries(state.addons);
      const addonsTotal = list.reduce((acc,[id,qty])=>{
        const price = Number(qs(`.addon-card[data-id="${id}"]`).dataset.price);
        return acc + price*qty*state.days;
      },0);
      const tax = Math.round((baseFrom+addonsTotal)*state.taxRate);
      const total = baseFrom + addonsTotal + tax;
      qs('#pBase').textContent = money(baseFrom);
      qs('#pAddons').textContent = money(addonsTotal);
      qs('#pTax').textContent = money(tax);
      qs('#pTotal').textContent = money(total);
    }
  }

  function renderConfirmation(){
    const code = 'MX-' + Math.random().toString(36).substring(2,8).toUpperCase();

    qs('#cfCode').textContent = code;
    qs('#cfStart').textContent = qs('#sumStart').textContent;
    qs('#cfEnd').textContent   = qs('#sumEnd').textContent;

    qs('#cfImg').src   = qs('#sumImg').src;
    qs('#cfName').textContent = qs('#sumName').textContent;
    qs('#cfCat').textContent  = qs('#sumCat').textContent;

    const extrasHTML = (qs('#sumExtras').innerHTML || '').trim();
    qs('#cfExtras').innerHTML = extrasHTML || 'Sin complementos';

    qs('#cfBase').textContent  = qs('#pBase').textContent;
    qs('#cfAddons').textContent= qs('#pAddons').textContent;
    qs('#cfTax').textContent   = qs('#pTax').textContent;
    qs('#cfTotal').textContent = qs('#pTotal').textContent;

    const priceBox = qs('.rf-price');
    const paidNow = state?.payment?.paid || 0;
    if (priceBox && !qs('#cfPaid')) {
      const row = document.createElement('div');
      row.className = 'price-row';
      row.innerHTML = `<span>Pagado hoy</span><strong id="cfPaid">${fmtMoney(paidNow)}</strong>`;
      priceBox.insertBefore(row, priceBox.querySelector('.price-row.total'));
    } else if (qs('#cfPaid')) {
      qs('#cfPaid').textContent = fmtMoney(paidNow);
    }

    const methodMap = { tarjeta:'Tarjeta', paypal:'PayPal', oxxo:'OXXO', mp:'Mercado Pago', efectivo:'Efectivo (en mostrador)' };
    const method = state?.payment?.method || 'tarjeta';

    const totalNum = Number(String(qs('#pTotal').textContent).replace(/[^\d.]/g,'')) || 0;
    const pending  = Math.max(0, totalNum - paidNow);

    qs('#rcCode').textContent    = code;
    qs('#rcMethod').textContent  = methodMap[method] || method;
    qs('#rcPaid').textContent    = fmtMoney(paidNow);
    qs('#rcPending').textContent = fmtMoney(pending);
    qs('#rcPeriod').textContent  = `${qs('#cfStart').textContent} — ${qs('#cfEnd').textContent}`;

    const rcStatus = qs('#rcStatus');
    if(method === 'efectivo'){
      rcStatus.textContent = 'Pago pendiente: se liquida en mostrador';
      rcStatus.classList.remove('ok'); rcStatus.classList.add('pending');
    }else if(pending > 0){
      rcStatus.textContent = `Anticipo aplicado (${state?.payment?.planPct || 0}%); saldo pendiente a la entrega`;
      rcStatus.classList.remove('ok'); rcStatus.classList.add('pending');
    }else{
      rcStatus.textContent = 'Pago completado';
      rcStatus.classList.remove('pending'); rcStatus.classList.add('ok');
    }

    qs('#printReceipt')?.addEventListener('click', ()=> window.print());

    qs('#step4').classList.add('hidden');
    qs('#confirmation').classList.remove('hidden');
    setActiveStep(4);
    window.scrollTo({top:0, behavior:'smooth'});
  }

  qs('#quoteBtn')?.addEventListener('click', (e)=>{
    e.preventDefault();
    const totalTxt = qs('#pTotal')?.textContent || '$0 MXN';
    const carName  = state.car?.name || '—';
    const period   = `${qs('#sumStart')?.textContent || '—'} → ${qs('#sumEnd')?.textContent || '—'}`;
    alert(`Cotización\n\nAuto: ${carName}\nPeriodo: ${period}\nTotal: ${totalTxt}`);
  });

  qs('#year').textContent = new Date().getFullYear();

  qs('#quoteBtn')?.addEventListener('click', (e)=>{
    e.preventDefault();
    qs('#qtLoc').textContent   = state.loc || '—';
    qs('#qtPeriod').textContent= `${qs('#sumStart').textContent} → ${qs('#sumEnd').textContent}`;
    qs('#qtDays').textContent  = `${state.days} días`;
    qs('#qtPlan').textContent  = state.isPreferred ? 'Miembro preferente' : 'Normal';
    qs('#qtStart').textContent = qs('#sumStart').textContent;
    qs('#qtEnd').textContent   = qs('#sumEnd').textContent;
    qs('#qtName').textContent  = state.car?.name || '—';
    qs('#qtCat').textContent   = `Categoría ${state.car?.cat || '—'}`;
    qs('#qtImg').src           = state.car?.img || '';
    qs('#qtExtras').innerHTML  = qs('#sumExtras').innerHTML;
    qs('#qtBase').textContent  = qs('#pBase').textContent;
    qs('#qtAddons').textContent= qs('#pAddons').textContent;
    qs('#qtTax').textContent   = qs('#pTax').textContent;
    qs('#qtTotal').textContent = qs('#pTotal').textContent;

    const code = 'QT-' + Math.random().toString(36).substring(2,8).toUpperCase();
    qs('#qtCode').textContent  = code;
    qs('#qtCode2').textContent = code;

    const qtSection = qs('#quoteTemplate');
    qtSection.style.display = 'block';

    const opt = {
      margin:       10,
      filename:     `Cotizacion_${code}.pdf`,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2 },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(qtSection).save().then(()=>{
    qtSection.style.display = 'none';
  });
});
