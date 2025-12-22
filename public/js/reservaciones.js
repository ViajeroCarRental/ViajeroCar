(function () {
  "use strict";

  // ===== Helpers =====
  const qs  = (s) => document.querySelector(s);
  const qsa = (s) => Array.from(document.querySelectorAll(s));

  // ==============================
  // 🧽 Normalizador de layout PDF
  // ==============================
  function normalizePdfLayout(root){
    if (!root) return;

    root.querySelectorAll('.topbar,.steps-header,.hamburger,.quote-actions,.link,.footer-elegant')
        .forEach(n => n.remove());

    const widen = [
      '.wrap','.page','.main','#cotizacionDoc','.quote-doc',
      '.summary-grid','.confirm-grid','.r-card','.r-price'
    ];
    root.querySelectorAll(widen.join(',')).forEach(el=>{
      el.style.display = 'block';
      el.style.width = '100%';
      el.style.maxWidth = '100%';
      el.style.margin = '0';
      el.style.borderRadius = '0';
      el.style.gridTemplateColumns = 'none';
      el.style.gridTemplateAreas = 'none';
    });

    root.querySelectorAll('.card,.quote-doc,.resume-card,.receipt-card,.resume-final')
        .forEach(el=>{
          el.style.boxShadow = 'none';
          el.style.border = '1px solid #e5e7eb';
        });

    root.querySelectorAll('img').forEach(img=>{
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
      img.style.display = 'block';
    });
  }

  // ---------- Topbar ----------
  function initTopbar() {
    const topbar = qs(".topbar");
    if (!topbar) return;

    function toggleTopbar() {
      if (window.scrollY > 40) topbar.classList.add("solid");
      else topbar.classList.remove("solid");
    }

    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });
  }

  // ---------- Progreso ----------
  function paintProgress() {
    const root = qs("main.page") || document.body;
    const stepNow = Number(root?.dataset?.currentStep || 1);

    const items = qsa(".steps-header .step-item");
    items.forEach((it) => {
      const n = Number(it.dataset.step || 0);
      it.classList.toggle("active", n === stepNow);
      it.classList.toggle("done", n < stepNow);
    });

    const total = items.length || 1;
    const fill = qs("#progressFill");
    if (fill) {
      const pct = ((Math.max(1, stepNow) - 1) / Math.max(1, total - 1)) * 100;
      fill.style.width = `${pct}%`;
    }
  }

  // ---------- Flatpickr ----------
  function initFlatpickrLite() {
    if (!window.flatpickr) return;

    try { if (flatpickr.l10ns?.es) flatpickr.localize(flatpickr.l10ns.es); } catch (_) {}

    const start = qs("#start");
    const end = qs("#end");
    if (start && end) {
      if (typeof rangePlugin !== "undefined") {
        flatpickr(start, {
          enableTime: true, time_24hr: false, minuteIncrement: 5,
          altInput: true, altFormat: "d/m/Y h:i K", dateFormat: "Y-m-d H:i",
          minDate: "today", plugins: [new rangePlugin({ input: "#end" })],
        });
      } else {
        flatpickr(start, {
          enableTime: true, time_24hr: false, minuteIncrement: 5,
          altInput: true, altFormat: "d/m/Y h:i K", dateFormat: "Y-m-d H:i", minDate: "today",
        });
        flatpickr(end, {
          enableTime: true, time_24hr: false, minuteIncrement: 5,
          altInput: true, altFormat: "d/m/Y h:i K", dateFormat: "Y-m-d H:i", minDate: "today",
        });
      }
    }

    const ufStartDate = qs("#ufStartDate");
    const ufEndDate   = qs("#ufEndDate");
    const ufStartTime = qs("#ufStartTime");
    const ufEndTime   = qs("#ufEndTime");
    const dob         = qs("#dob");

    if (ufStartDate) flatpickr(ufStartDate, { altInput: true, altFormat: "d/m/Y", dateFormat: "Y-m-d" });
    if (ufEndDate)   flatpickr(ufEndDate,   { altInput: true, altFormat: "d/m/Y", dateFormat: "Y-m-d" });

    if (ufStartTime) flatpickr(ufStartTime, {
      enableTime: true, noCalendar: true, minuteIncrement: 5, time_24hr: false,
      altInput: true, altFormat: "h:i K", dateFormat: "H:i"
    });

    if (ufEndTime) flatpickr(ufEndTime, {
      enableTime: true, noCalendar: true, minuteIncrement: 5, time_24hr: false,
      altInput: true, altFormat: "h:i K", dateFormat: "H:i"
    });

    if (dob) flatpickr(dob, { altInput: true, altFormat: "d/m/Y", dateFormat: "Y-m-d", maxDate: new Date() });
  }

  // ==============================
  // ✅ Step 3: Complementos
  // ==============================
  function initAddonsStep3() {
    const step3 = qs("#step3");
    if (!step3) return;

    const grid = qs(".addons-grid");
    const btnContinue = qs("#toStep4");
    const btnSkip = qs("#skipAddons");

    if (!grid || !btnContinue) return;

    const state = new Map();

    function getCardData(card) {
      const id     = String(card.dataset.id || "");
      const name   = String(card.dataset.name || "");
      const price  = Number(card.dataset.price || 0);
      const charge = String(card.dataset.charge || "por_dia");
      const stock  = card.dataset.stock ? Number(card.dataset.stock) : Infinity;
      return { id, name, price, charge, stock };
    }

    function getQtyEl(card) { return card.querySelector(".qty"); }

    function updateCardUI(card, qty) {
      const qtyEl = getQtyEl(card);
      if (qtyEl) qtyEl.textContent = String(qty);
      card.classList.toggle("selected", qty > 0);
    }

    function serializeState() {
      const obj = {};
      state.forEach((v, id) => {
        if (v.qty > 0) obj[id] = { id, name: v.name, price: v.price, charge: v.charge, qty: v.qty };
      });
      return obj;
    }

    function persistSelection() {
      try { sessionStorage.setItem('addons_selection', JSON.stringify(serializeState())); } catch(_) {}
    }

    function loadSelection() {
      try {
        const saved = JSON.parse(sessionStorage.getItem('addons_selection') || '{}');
        Object.values(saved).forEach(it => {
          state.set(String(it.id), {
            id: String(it.id),
            name: it.name,
            price: Number(it.price),
            charge: String(it.charge),
            stock: Infinity,
            qty: Number(it.qty)
          });
          const card = grid.querySelector(`.addon-card[data-id="${it.id}"]`);
          if (card) updateCardUI(card, it.qty);
        });
      } catch(_) {}
    }

    function updateContinueHref() {
      try {
        const url = new URL(btnContinue.href, window.location.origin);

        [...url.searchParams.keys()]
          .filter(k => k.startsWith("addons["))
          .forEach(k => url.searchParams.delete(k));

        state.forEach((v, id) => {
          if (v.qty > 0) url.searchParams.set(`addons[${id}]`, String(v.qty));
        });

        btnContinue.href = url.toString();
      } catch (_) {}
    }

    function updateContinueButtonVisual() {
      btnContinue.classList.remove("is-disabled");
      btnContinue.removeAttribute("aria-disabled");
      btnContinue.removeAttribute("disabled");
    }

    // ✅ FIX REAL: navegar a Step 4 usando la URL actual + step=4 + addons[...] (no confiar solo en href)
    function goToStep4Hard() {
      const main = qs('main.page');
      const plan = main?.dataset?.plan || qs('#plan')?.value || '';
      const categoriaId = qs('#categoria_id')?.value || '';

      const url = new URL(window.location.href);
      url.searchParams.set('step', '4');

      // si por alguna razón faltan:
      if (plan && !url.searchParams.get('plan')) url.searchParams.set('plan', plan);
      if (categoriaId && !url.searchParams.get('categoria_id')) url.searchParams.set('categoria_id', categoriaId);

      // limpia addons previos
      [...url.searchParams.keys()]
        .filter(k => k.startsWith("addons["))
        .forEach(k => url.searchParams.delete(k));

      // agrega addons
      state.forEach((v, id) => {
        if (v.qty > 0) url.searchParams.set(`addons[${id}]`, String(v.qty));
      });

      window.location.href = url.toString();
    }

    loadSelection();
    updateContinueHref();
    updateContinueButtonVisual();

    grid.addEventListener("click", (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement)) return;

      const isPlus  = t.classList.contains("plus");
      const isMinus = t.classList.contains("minus");
      if (!isPlus && !isMinus) return;

      const card = t.closest(".addon-card");
      if (!card) return;

      const data = getCardData(card);
      if (!data.id) return;

      const current = state.get(data.id) || { ...data, qty: 0 };
      let nextQty = current.qty + (isPlus ? 1 : -1);

      if (nextQty < 0) nextQty = 0;
      if (Number.isFinite(data.stock)) nextQty = Math.min(nextQty, data.stock);

      current.qty = nextQty;
      state.set(data.id, current);

      updateCardUI(card, nextQty);
      updateContinueHref();
      updateContinueButtonVisual();
      persistSelection();
    });

    // ✅ IMPORTANTE: interceptamos click para forzar step=4
    btnContinue.addEventListener("click", (e) => {
      e.preventDefault();
      persistSelection();
      goToStep4Hard();
    });

    if (btnSkip) {
      btnSkip.addEventListener("click", () => {
        try { sessionStorage.removeItem('addons_selection'); } catch(_) {}
      });
    }
  }

  // ==============================
  // ✅ Step 4: hidratar resumen
  // ==============================
  function hydrateSummaryStep4() {
    const step4 = qs('#step4');
    if (!step4) return;

    const elList   = qs('#extrasList');
    const elEmpty  = qs('#extrasEmpty');
    const elBase   = qs('#qBase');
    const elExtras = qs('#qExtras');
    const elIva    = qs('#qIva');
    const elTotal  = qs('#qTotal');

    function parseMoney(text) { return Number(String(text).replace(/[^\d.]/g, '')) || 0; }

    const baseMx = elBase ? parseMoney(elBase.textContent) : 0;
    const days   = Number(qs('#qDays')?.textContent || '1') || 1;

    let selection = {};
    try { selection = JSON.parse(sessionStorage.getItem('addons_selection') || '{}'); }
    catch (_) { selection = {}; }

    let extrasSub = 0;
    if (elList) elList.innerHTML = '';

    const items = Object.values(selection);
    if (items.length === 0) {
      if (elEmpty) elEmpty.style.display = '';
    } else {
      if (elEmpty) elEmpty.style.display = 'none';
      items.forEach(it => {
        const qty = Number(it.qty || 0);
        if (qty <= 0) return;

        const price = Number(it.price || 0);
        const isPerDay = (String(it.charge || '') === 'por_dia');
        const sub = price * (isPerDay ? days : 1) * qty;

        extrasSub += sub;

        const li = document.createElement('li');
        li.innerHTML = `<span>${it.name} ${isPerDay ? '(por día)' : '(evento)'} × ${qty}</span><strong>$${sub.toLocaleString()} MXN</strong>`;
        elList?.appendChild(li);
      });
    }

    if (elExtras) elExtras.textContent = `$${extrasSub.toLocaleString()} MXN`;

    const iva = Math.round((baseMx + extrasSub) * 0.16);
    if (elIva) elIva.textContent = `$${iva.toLocaleString()} MXN`;

    if (elTotal) elTotal.textContent = `$${(baseMx + extrasSub + iva).toLocaleString()} MXN`;
  }

  // ======================================================
  // === Guardar cotización vía AJAX + generar PDF
  // ======================================================
  function initPdfFlow() {
    const H2C_URL   = "https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js";
    const JSPDF_URL = "https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js";

    const PAGE_W = 816;
    const PAGE_H = 1056;

    const MARGIN = { top: 64, right: 64, bottom: 64, left: 64 };
    const CONTENT_W = PAGE_W - MARGIN.left - MARGIN.right;

    const btnPdf = qs('#btnCotizar');
    if (!btnPdf) return;
    if (btnPdf.__pdfBound) return;
    btnPdf.__pdfBound = true;

    function loadScript(src){
      return new Promise((res, rej)=>{
        const s = document.createElement('script');
        s.src = src; s.async = true; s.onload = res; s.onerror = rej;
        document.head.appendChild(s);
      });
    }

    function libs(){
      const h2c = window.html2canvas?.default || window.html2canvas || null;
      const jsPDFCtor = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF || null;
      return { h2c, jsPDFCtor };
    }

    async function ensureLibs(){
      let { h2c, jsPDFCtor } = libs();
      if (!h2c)      { await loadScript(H2C_URL).catch(()=>{});      ({ h2c } = libs()); }
      if (!jsPDFCtor){ await loadScript(JSPDF_URL).catch(()=>{}); ({ jsPDFCtor } = libs()); }
      return { h2c, jsPDFCtor };
    }

    function absolutize(url) { try { return new URL(url, window.location.origin).href; } catch { return url; } }

    async function ensureCarImageCORS(container){
      const img = container.querySelector('.car-sum img, .car-mini__img img, .r-media img');
      if (!img) return;
      const abs = absolutize(img.getAttribute('src') || '');
      img.setAttribute('crossorigin','anonymous');
      img.src = abs;
      if (!img.complete) {
        await new Promise(r=>{
          img.addEventListener('load', r, {once:true});
          img.addEventListener('error', r, {once:true});
        });
      }
    }

    function ensureQuoteHeader(container){
      const head = container.querySelector('.qd-head');
      if (!head) return;
      // Tu Blade ya trae folio/fecha, no inventamos nada aquí.
    }

    function getCsrfToken() {
      const form = qs('#formCotizacion');
      const fromInput = form?.querySelector('input[name="_token"]')?.value;
      if (fromInput) return fromInput;
      const fromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      return fromMeta || '';
    }

    function getUrlParams() {
      const url = new URL(window.location.href);
      const sp  = url.searchParams;
      const pickup_sucursal_id  = sp.get('pickup_sucursal_id') || '';
      const dropoff_sucursal_id = sp.get('dropoff_sucursal_id') || '';
      const categoria_id        = sp.get('categoria_id') || '';
      const vehiculo_id         = sp.get('vehiculo_id') || '';
      return { pickup_sucursal_id, dropoff_sucursal_id, categoria_id, vehiculo_id };
    }

    function getAddonsForPost() {
      try {
        const raw = JSON.parse(sessionStorage.getItem('addons_selection') || '{}');
        const out = {};
        Object.values(raw).forEach(it => {
          const qty = Number(it.qty || 0);
          if (qty > 0) out[String(it.id)] = qty;
        });
        return out;
      } catch {
        return {};
      }
    }

    async function saveCotizacionBeforePdf() {
      const form = qs('#formCotizacion');
      if (!form) return { ok: false, message: 'No se encontró el formulario.' };

      const nombre   = qs('#nombreCliente')?.value?.trim()  || '';
      const email    = qs('#correoCliente')?.value?.trim()   || '';
      const telefono = qs('#telefonoCliente')?.value?.trim() || '';

      let pickup_date  = qs('#pickup_date')?.value || '';
      let pickup_time  = qs('#pickup_time')?.value || '';
      let dropoff_date = qs('#dropoff_date')?.value || '';
      let dropoff_time = qs('#dropoff_time')?.value || '';

      if (!pickup_date || !pickup_time) {
        const briefStart = qs('#briefStart')?.textContent?.trim() || '';
        const parts = briefStart.split(/\s+/);
        if (parts.length >= 2) { pickup_date = parts[0]; pickup_time = parts[1]; }
      }
      if (!dropoff_date || !dropoff_time) {
        const briefEnd = qs('#briefEnd')?.textContent?.trim() || '';
        const parts = briefEnd.split(/\s+/);
        if (parts.length >= 2) { dropoff_date = parts[0]; dropoff_time = parts[1]; }
      }

      const { pickup_sucursal_id, dropoff_sucursal_id, vehiculo_id } = getUrlParams();
      const addons = getAddonsForPost();

      const payload = {
        vehiculo_id: Number(vehiculo_id) || undefined,
        pickup_date, pickup_time, dropoff_date, dropoff_time,
        pickup_sucursal_id: pickup_sucursal_id ? Number(pickup_sucursal_id) : undefined,
        dropoff_sucursal_id: dropoff_sucursal_id ? Number(dropoff_sucursal_id) : undefined,
        addons,
        nombre, email, telefono,
        metodo_pago: "mostrador"
      };

      const url = form.getAttribute('action') || '/cotizaciones';
      const token = getCsrfToken();

      const original = btnPdf.innerHTML;
      btnPdf.disabled = true;
      btnPdf.innerHTML = '<span class="spinner" style="display:inline-block;transform:translateY(2px)">⏳</span> Generando...';

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || data?.ok === false) {
          const msg = data?.message || 'No se pudo guardar la cotización.';
          throw new Error(msg);
        }

        try {
          const telefonoAgente = "5214427169793";
          const folioTxt = data?.folio ? data.folio : 'pendiente';
          const mensaje =
            `*NUEVA COTIZACIÓN*\n\n` +
            `👤 *Cliente:* ${nombre || 'No especificado'}\n` +
            `🧾 *Folio de cotización:* ${folioTxt}\n` +
            `📅 *Fecha de entrega:* ${pickup_date} ${pickup_time}\n` +
            `📆 *Fecha de devolución:* ${dropoff_date} ${dropoff_time}\n\n` +
            `Mensaje predeterminado del sistema Viajero Car Rental.`;
          const mensajeEncoded = encodeURIComponent(mensaje);
          const waWebUrl = `https://web.whatsapp.com/send?phone=${telefonoAgente}&text=${mensajeEncoded}`;
          window.open(waWebUrl, '_blank');
        } catch (err) {
          console.error('Error al abrir WhatsApp Web:', err);
        }

        return { ok: true, folio: data?.folio || null };
      } catch (err) {
        console.error('Error guardando cotización:', err);
        alert('No se pudo guardar/enviar la cotización. Revisa tu conexión o inténtalo más tarde.');
        return { ok: false, message: String(err?.message || err) };
      } finally {
        btnPdf.disabled = false;
        btnPdf.innerHTML = original;
      }
    }

    async function generatePdfFlow() {
      const node = qs('#cotizacionDoc');
      if (!node) return;

      const { h2c, jsPDFCtor } = await ensureLibs();
      if (!h2c || !jsPDFCtor) {
        alert('No pude cargar el generador de PDF. Revisa tu conexión e inténtalo de nuevo.');
        return;
      }

      ensureQuoteHeader(node);

      const interactive = qsa('a,button', node);
      const prev = new Map();
      interactive.forEach(el=>{ prev.set(el, el.style.display); el.style.display='none'; });

      document.body.classList.add('for-pdf');

      try{
        await ensureCarImageCORS(node);

        const clone = node.cloneNode(true);
        clone.classList.add('pdf-fit');
        clone.style.width = `${CONTENT_W}px`;
        clone.style.maxWidth = `${CONTENT_W}px`;
        normalizePdfLayout(clone);

        let sandbox = document.getElementById('pdf-sandbox');
        if (!sandbox) {
          sandbox = document.createElement('div');
          sandbox.id = 'pdf-sandbox';
          document.body.appendChild(sandbox);
        } else {
          sandbox.innerHTML = '';
        }

        sandbox.style.width = `${CONTENT_W}px`;
        sandbox.appendChild(clone);

        window.scrollTo({top:0, behavior:'auto'});

        const canvas = await h2c(clone, {
          useCORS: true,
          allowTaint: false,
          backgroundColor: '#ffffff',
          scale: 2,
          width: CONTENT_W,
          windowWidth: CONTENT_W,
          scrollY: 0
        });

        const pdf = new jsPDFCtor({ unit:'px', format:[PAGE_W, PAGE_H], orientation:'portrait' });

        const boxW = PAGE_W - MARGIN.left - MARGIN.right;
        const boxH = PAGE_H - MARGIN.top  - MARGIN.bottom;

        const imgW = canvas.width;
        const imgH = canvas.height;
        const fit  = Math.min(boxW / imgW, boxH / imgH);

        const outW = Math.round(imgW * fit);
        const outH = Math.round(imgH * fit);

        const offsetX = Math.round(MARGIN.left + (boxW - outW) / 2);
        const offsetY = Math.round(MARGIN.top  + (boxH - outH) / 2);

        const imgData = canvas.toDataURL('image/jpeg', 0.98);
        const fileName = (qs('#cotizacionDoc .qd-meta .v')?.textContent || 'cotizacion') + '.pdf';

        pdf.addImage(imgData, 'JPEG', offsetX, offsetY, outW, outH);
        pdf.save(fileName);

        const sb = document.getElementById('pdf-sandbox');
        if (sb && sb.parentNode) sb.parentNode.removeChild(sb);
      } catch(err){
        console.error('PDF error:', err);
        alert('Hubo un error generando el PDF. Revisa la consola para detalles.');
      } finally {
        interactive.forEach(el=>{ el.style.display = prev.get(el) || ''; });
        document.body.classList.remove('for-pdf');
      }
    }

    btnPdf.addEventListener('click', async ()=>{
      const saved = await saveCotizacionBeforePdf();
      if (!saved.ok) return;
      await generatePdfFlow();
    });
  }

  // ---------- Footer year ----------
  function setYear() {
    const y = qs("#year");
    if (y) y.textContent = new Date().getFullYear();
  }

  // ==============================
  // ✅ BOOT
  // ==============================
  document.addEventListener("DOMContentLoaded", () => {
    initTopbar();
    paintProgress();
    initFlatpickrLite();
    initAddonsStep3();
    hydrateSummaryStep4();
    initPdfFlow();
    setYear();
  });

})();
