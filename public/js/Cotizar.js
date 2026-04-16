/* ==========================================================
   🚗 COTIZACIONES - SISTEMA COMPLETO
   ========================================================= */

/* ==========================================================
   1️⃣ UTILIDADES Y HELPERS
========================================================== */
const qs = (s) => document.querySelector(s);
const qsa = (s) => Array.from(document.querySelectorAll(s));

const money = (n) => {
  const num = Number(n || 0);
  return `$${num.toLocaleString("es-MX", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })} MXN`;
};

const openPop = (el) => { if (el) el.style.display = "flex"; };
const closePop = (el) => { if (el) el.style.display = "none"; };

function escapeHtml(str) {
  if (!str) return "";
  return str.replace(/[&<>]/g, function(m) {
    if (m === "&") return "&amp;";
    if (m === "<") return "&lt;";
    if (m === ">") return "&gt;";
    return m;
  });
}

function formatISODate(d) {
  if (!(d instanceof Date) || isNaN(d)) return "";
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const da = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${da}`;
}

/* ==========================================================
   2️⃣ ESTADO GLOBAL
========================================================== */
const state = {
  days: 0,
  categoria: null,
  proteccion: null,
  addons: new Map(),
  moneda: "MXN",
  tc: 17,
  tarifaOriginal: 0,
  tarifaEditada: false
};

/* ==========================================================
   2.5️⃣ FUNCIÓN DE ALERTA PEQUEÑA (TOAST)
========================================================== */
function mostrarToast(mensaje, tipo = 'warning') {
    if (typeof alertify !== 'undefined') {
        switch(tipo) {
            case 'warning':
                alertify.warning(mensaje);
                break;
            case 'error':
                alertify.error(mensaje);
                break;
            case 'success':
                alertify.success(mensaje);
                break;
            default:
                alertify.warning(mensaje);
        }
    } else {
        const toast = document.createElement('div');
        toast.textContent = mensaje;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${tipo === 'warning' ? '#f59e0b' : tipo === 'error' ? '#ef4444' : '#10b981'};
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

/* ==========================================================
   3️⃣ FLATPICKR MODAL
========================================================== */
function initFlatpickrModal() {
  if (!window.flatpickr) {
    console.warn('Flatpickr no está cargado');
    return;
  }

  let backdrop = document.querySelector(".fp-backdrop");
  if (!backdrop) {
    backdrop = document.createElement("div");
    backdrop.className = "fp-backdrop";
    document.body.appendChild(backdrop);
  }

  function makeActions(instance, labelText) {
    const actions = document.createElement("div");
    actions.className = "fp-actions";
    actions.innerHTML = `
      <button type="button" class="fp-today">Hoy</button>
      <button type="button" class="fp-clear">Limpiar</button>
      <button type="button" class="fp-label">✖ ${labelText}</button>
    `;

    actions.querySelector(".fp-today").addEventListener("click", (e) => {
      e.stopPropagation();
      instance.setDate(new Date(), true);
    });

    actions.querySelector(".fp-clear").addEventListener("click", (e) => {
      e.stopPropagation();
      instance.clear();
      if (instance.input?.id === "fecha_inicio_ui") {
        document.getElementById("fecha_inicio").value = "";
      }
      if (instance.input?.id === "fecha_fin_ui") {
        document.getElementById("fecha_fin").value = "";
      }
      if (typeof calcularDias === 'function') calcularDias();
      if (typeof actualizarResumenViaje === 'function') actualizarResumenViaje();
    });

    return actions;
  }

  function openModal(instance) {
    backdrop.classList.add("is-open");
    document.body.style.overflow = "hidden";
    backdrop.onclick = () => instance.close();
  }

  function closeModal() {
    backdrop.classList.remove("is-open");
    document.body.style.overflow = "";
    backdrop.onclick = null;
  }

  function centerCalendar(instance) {
    if (instance && instance.calendarContainer) {
      instance.calendarContainer.style.position = 'fixed';
      instance.calendarContainer.style.top = '50%';
      instance.calendarContainer.style.left = '50%';
      instance.calendarContainer.style.transform = 'translate(-50%, -50%)';
    }
  }

  const inicioPicker = flatpickr("#fecha_inicio_ui", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-M-y",
    allowInput: false,
    clickOpens: true,
    minDate: "today",
    onOpen: (sel, str, instance) => {
      const sucRetiro = document.getElementById("sucursal_retiro")?.value;
      const sucEntrega = document.getElementById("sucursal_entrega")?.value;

      if (!sucRetiro || !sucEntrega) {
        mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
        instance.close();
        return false;
      }

      openModal(instance);
      centerCalendar(instance);
      if (!instance._actionsAdded) {
        instance.calendarContainer.appendChild(makeActions(instance, "Fecha recogida"));
        instance._actionsAdded = true;
      }
    },
    onReady: (sel, str, instance) => {
      centerCalendar(instance);
    },
    onClose: () => closeModal(),
    onChange: (selectedDates) => {
      const d = selectedDates?.[0];
      const fechaInicioHidden = document.getElementById("fecha_inicio");
      if (fechaInicioHidden) {
        fechaInicioHidden.value = d ? formatISODate(d) : "";
      }
      if (typeof calcularDias === 'function') calcularDias();
      if (typeof actualizarResumenViaje === 'function') actualizarResumenViaje();

      if (finishPicker && selectedDates[0]) {
        const minDate = new Date(selectedDates[0]);
        minDate.setDate(minDate.getDate() + 1);
        finishPicker.set("minDate", minDate);
      }
    }
  });

  let finishPicker = flatpickr("#fecha_fin_ui", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d-M-y",
    allowInput: false,
    clickOpens: true,
    minDate: "today",
    onOpen: (sel, str, instance) => {
      const sucRetiro = document.getElementById("sucursal_retiro")?.value;
      const sucEntrega = document.getElementById("sucursal_entrega")?.value;
      const fechaInicio = document.getElementById("fecha_inicio")?.value;

      if (!sucRetiro || !sucEntrega) {
        mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
        instance.close();
        return false;
      }

      if (!fechaInicio) {
        mostrarToast('⚠️ Primero debes seleccionar la FECHA DE SALIDA', 'warning');
        instance.close();
        return false;
      }

      openModal(instance);
      centerCalendar(instance);
      if (!instance._actionsAdded) {
        instance.calendarContainer.appendChild(makeActions(instance, "Fecha devolución"));
        instance._actionsAdded = true;
      }
    },
    onReady: (sel, str, instance) => {
      centerCalendar(instance);
    },
    onClose: () => closeModal(),
    onChange: (selectedDates) => {
      const d = selectedDates?.[0];
      const fechaFinHidden = document.getElementById("fecha_fin");
      if (fechaFinHidden) {
        fechaFinHidden.value = d ? formatISODate(d) : "";
      }
      if (typeof calcularDias === 'function') calcularDias();
      if (typeof actualizarResumenViaje === 'function') actualizarResumenViaje();
    }
  });

  function initTimeSelectors() {
    const horaRetiroInput = document.getElementById("hora_retiro_ui");
    const horaRetiroHidden = document.getElementById("hora_retiro");

    if (horaRetiroInput && !horaRetiroInput.dataset.tpReady) {
      horaRetiroInput.dataset.tpReady = "1";
      horaRetiroInput.setAttribute("readonly", "readonly");
      horaRetiroInput.classList.add("tp-hidden-input");
      createTimeSelectsBelow(horaRetiroInput, horaRetiroHidden, "Hora");
    }

    const horaEntregaInput = document.getElementById("hora_entrega_ui");
    const horaEntregaHidden = document.getElementById("hora_entrega");

    if (horaEntregaInput && !horaEntregaInput.dataset.tpReady) {
      horaEntregaInput.dataset.tpReady = "1";
      horaEntregaInput.setAttribute("readonly", "readonly");
      horaEntregaInput.classList.add("tp-hidden-input");
      createTimeSelectsBelow(horaEntregaInput, horaEntregaHidden, "Hora");
    }
  }

  function createTimeSelectsBelow(input, hiddenInput, placeholderText) {
    const wrap = input.closest(".time-field") || input.parentElement;
    if (!wrap) return;
    if (wrap.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects";

    const selH = document.createElement("select");
    selH.className = "tp-hour";
    selH.setAttribute("aria-label", placeholderText);

    selH.innerHTML = '<option value="" disabled selected>' + placeholderText + '</option>';
    for (let h = 0; h < 24; h++) {
      const hour = String(h).padStart(2, "0");
      const option = document.createElement("option");
      option.value = hour;
      option.textContent = `${hour}:00`;
      selH.appendChild(option);
    }

    box.appendChild(selH);
    wrap.appendChild(box);

    if (!hiddenInput || !hiddenInput.value) {
      selH.value = "";
      if (hiddenInput) hiddenInput.value = "";
      input.value = "";
      input.placeholder = "Hora";
    } else {
      const existingHour = hiddenInput.value.split(":")[0];
      if (existingHour && Array.from(selH.options).some(opt => opt.value === existingHour)) {
        selH.value = existingHour;
        input.value = hiddenInput.value;
      } else {
        selH.value = "";
        if (hiddenInput) hiddenInput.value = "";
        input.value = "";
        input.placeholder = "Hora";
      }
    }

    function sync() {
      if (!selH.value) {
        if (hiddenInput) hiddenInput.value = "";
        input.value = "";
        if (typeof actualizarResumenViaje === 'function') actualizarResumenViaje();
        return;
      }
      const finalHour = String(selH.value).padStart(2, "0");
      const timeValue = `${finalHour}:00`;
      if (hiddenInput) hiddenInput.value = timeValue;
      input.value = timeValue;
      if (typeof actualizarResumenViaje === 'function') actualizarResumenViaje();
    }

    selH.addEventListener("change", sync);

    // VALIDACIÓN: Mostrar toast si intenta abrir el select sin ubicación
    selH.addEventListener("click", function(e) {
      const sucRetiro = document.getElementById("sucursal_retiro")?.value;
      const sucEntrega = document.getElementById("sucursal_entrega")?.value;
      const fechaInicio = document.getElementById("fecha_inicio")?.value;

      if (!sucRetiro || !sucEntrega) {
        e.stopPropagation();
        mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
        this.blur();
        return false;
      }

      if (!fechaInicio) {
        e.stopPropagation();
        mostrarToast('⚠️ Primero debes seleccionar la FECHA DE SALIDA', 'warning');
        this.blur();
        return false;
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTimeSelectors);
  } else {
    initTimeSelectors();
  }

  return { inicioPicker, finishPicker };
}

/* ==========================================================
   4️⃣ NAVEGACIÓN ENTRE PASOS
========================================================== */
const showStep = (n) => {
  qsa("[data-step]").forEach((el) => {
    el.style.display = Number(el.dataset.step) === n ? "block" : "none";
  });
};

document.getElementById("go2")?.addEventListener("click", () => showStep(2));
document.getElementById("back1")?.addEventListener("click", () => showStep(1));
document.getElementById("go3")?.addEventListener("click", () => showStep(3));
document.getElementById("back2")?.addEventListener("click", () => showStep(2));
showStep(1);

/* ==========================================================
   5️⃣ CÁLCULO DE DÍAS
========================================================== */
function calcularDias() {
  const f1 = document.getElementById("fecha_inicio")?.value;
  const f2 = document.getElementById("fecha_fin")?.value;

  if (!f1 || !f2) return 0;

  const date1 = new Date(f1);
  const date2 = new Date(f2);

  if (isNaN(date1) || isNaN(date2)) return 0;

  const diff = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
  const days = diff > 0 ? diff : 1;

  const diasTxt = document.getElementById("diasTxt");
  if (diasTxt) diasTxt.textContent = days;
  state.days = days;

  refreshCategoriaPreview();
  repaintCategoriaModalEstimados();
  actualizarTotal();

  return days;
}


/* ==========================================================
   6️⃣ CATEGORÍA
========================================================== */
function setCategoria(cat) {
  state.categoria = cat;
  state.tarifaOriginal = cat?.precio_dia || 0;

  const hid = document.getElementById("categoria_id");
  if (hid) hid.value = cat ? String(cat.id) : "";

  const txt = document.getElementById("catSelTxt");
  const sub = document.getElementById("catSelSub");
  const rem = document.getElementById("catRemove");
  const mini = document.getElementById("catMiniPreview");

  if (!cat) {
    if (txt) txt.textContent = "— Ninguna categoría —";
    if (sub) sub.textContent = "Tarifa base por día y cálculo previo aparecerán aquí.";
    if (rem) rem.style.display = "none";
    if (mini) mini.style.display = "none";
    actualizarTotal();
    return;
  }

  if (txt) txt.textContent = cat.nombre;
  if (sub) sub.textContent = `${money(cat.precio_dia)} / día · ${state.days || 0} día(s)`;
  if (rem) rem.style.display = "";
  if (mini) mini.style.display = "";

  refreshCategoriaPreview();
  actualizarTotal();
}

function refreshCategoriaPreview() {
  const cat = state.categoria;
  const mini = document.getElementById("catMiniPreview");
  if (!mini) return;

  if (!cat) {
    mini.style.display = "none";
    return;
  }

  mini.style.display = "";

  const n = document.getElementById("catMiniName");
  const d = document.getElementById("catMiniDesc");
  const rate = document.getElementById("catMiniRate");
  const calc = document.getElementById("catMiniCalc");

  if (n) n.textContent = cat.nombre || "—";
  if (d) d.textContent = cat.desc || "—";
  if (rate) rate.textContent = `${money(cat.precio_dia).replace(" MXN", "")} MXN / día`;

  const pre = Number(cat.precio_dia || 0) * Number(state.days || 0);
  if (calc) calc.textContent = money(pre);
}

function repaintCategoriaModalEstimados() {
  const dias = Number(state.days || 0);
  const cards = document.querySelectorAll("#catPop .card-pick");
  if (!cards.length) return;

  cards.forEach((card) => {
    const precio = Number(card.dataset.precio || 0);
    const est = precio * Math.max(dias, 0);
    const estimadoEl = card.querySelector(".cat-estimado");
    if (estimadoEl) estimadoEl.textContent = `$${est.toFixed(2)}`;
  });
}

/* ==========================================================
   7️⃣ CARGAR CATEGORÍAS
========================================================== */
async function cargarCategorias() {
  const grid = document.getElementById("categoriasGrid");
  if (!grid) return;

  grid.innerHTML = '<div class="loading">Cargando categorías...</div>';

  try {
    const res = await fetch("/admin/cotizaciones/categorias");
    const categorias = await res.json();

    if (!categorias.length) {
      grid.innerHTML = '<div class="loading">No hay categorías disponibles.</div>';
      return;
    }

    const imgCategorias = {
      1: '/img/aveo.png', 2: '/img/virtus.png', 3: '/img/jetta.png',
      4: '/img/camry.png', 5: '/img/renegade.png', 6: '/img/taos.png',
      7: '/img/avanza.png', 8: '/img/Odyssey.png', 9: '/img/Urvan.png',
      10: '/img/Frontier.png', 11: '/img/Tacoma.png',
    };

    const pasajeros = {
      1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 5,
      7: 7, 8: 8, 9: 13, 10: 5, 11: 5,
    };

    const transmision = { 9: 'Manual' };

    const categoriasOrdenadas = [...categorias].sort((a, b) =>
      (parseFloat(a.precio_dia || 0) - parseFloat(b.precio_dia || 0))
    );

    grid.innerHTML = categoriasOrdenadas.map(cat => {
      const img = imgCategorias[cat.id_categoria] || '/assets/Logotipo.png';
      const cap = pasajeros[cat.id_categoria] || 5;
      const tran = transmision[cat.id_categoria] || 'Automático';
      const precioDia = parseFloat(cat.precio_dia || 0);
      const estimado = precioDia * (state.days || 1);

      const features = [
        { icon: 'bx bx-infinite', text: "Km ilimitados" },
        { icon: 'bx bx-shield-quarter', text: "Relevo responsabilidad" },
        { icon: 'bx bx-user', text: `${cap} pasajeros` },
        { icon: 'bx bxl-apple', text: "Apple CarPlay", class: "chip-apple" },
        { icon: 'bx bxl-android', text: "Android Auto", class: "chip-android" },
        { icon: 'bx bx-wind', text: "Aire acondicionado" },
        { icon: (tran === 'Manual' ? 'bx bx-joystick' : 'bx bx-cog'), text: tran },
      ];

      return `
        <article class="card-pick cat-wide"
          data-id="${cat.id_categoria}"
          data-nombre="${escapeHtml(cat.nombre)}"
          data-desc="${escapeHtml(cat.descripcion || '')}"
          data-precio="${precioDia}"
          data-precio-km="${cat.costo_km || 0}"
          data-img="${img}">
          <div class="cp-img">
            <img src="${img}" alt="${escapeHtml(cat.nombre)}">
          </div>
          <div class="cp-left">
            <div class="cp-title">${escapeHtml(cat.nombre)}</div>
            <div class="cp-sub">${escapeHtml(cat.descripcion || '')}</div>
            <div class="cp-features">
              ${features.map(f => `
                <span class="cp-chip ${f.class || ''}">
                  <i class='${f.icon}'></i>
                  <span>${f.text}</span>
                </span>
              `).join('')}
            </div>
            <div class="cp-meta">
              <span class="pill">Código: ${cat.codigo || cat.id_categoria}</span>
              <span class="pill pill-ok">Activo</span>
            </div>
          </div>
          <div class="cp-right">
            <div class="cp-price">
              <div class="muted small">Tarifa base</div>
              <div class="price-big">$${precioDia.toFixed(2)} <span>/ día</span></div>
            </div>
            <div class="cp-price">
              <div class="muted small">Estimado</div>
              <div class="price-big"><span class="cat-estimado">$${estimado.toFixed(2)}</span> <span>MXN</span></div>
            </div>
            <button class="btn primary btn-block" type="button">Elegir</button>
          </div>
        </article>
      `;
    }).join("");

  } catch (err) {
    console.error("Error:", err);
    grid.innerHTML = '<div class="loading">Error al cargar categorías.</div>';
  }
}

/* ==========================================================
   8️⃣ PROTECCIONES
========================================================== */
async function cargarProtecciones() {
  const list = document.getElementById("proteList");
  if (!list) return;

  list.innerHTML = '<div class="loading">Cargando protecciones...</div>';

  try {
    const res = await fetch("/admin/cotizaciones/seguros");
    let data = await res.json();

    if (!data || !data.length) {
      list.innerHTML = '<div class="loading">No hay protecciones disponibles.</div>';
      return;
    }

    const proteccionesActivas = data.filter(p =>
      !p.nombre.toUpperCase().includes("DECLINE")
    );

    proteccionesActivas.sort((a, b) => Number(b.precio_por_dia) - Number(a.precio_por_dia));

    list.innerHTML = `
      <div class="prote-content-wrapper" style="position: relative; padding-top: 50px;">
        <div class="prote-carousel">
          ${proteccionesActivas.map(p => {
            let listaHtml = '';
            if (p.descripcion) {
              let puntos = p.descripcion
                .split(/\r?\n|-/)
                .map(linea => linea.trim())
                .filter(linea => linea.length > 0 && linea !== '-');

              if (puntos.length === 1 && !p.descripcion.includes('-') && p.descripcion.includes('.')) {
                puntos = p.descripcion
                  .split('.')
                  .map(linea => linea.trim())
                  .filter(linea => linea.length > 0);
              }

              listaHtml = puntos.length > 0
                ? `<ul class="lista-protecciones">${puntos.map(punto => `<li>${escapeHtml(punto)}</li>`).join('')}</ul>`
                : `<p>${escapeHtml(p.descripcion)}</p>`;
            }

            return `
              <div class="seg-card" data-id="${p.id_paquete}" data-nombre="${escapeHtml(p.nombre)}" data-precio="${p.precio_por_dia}">
                <h4>${escapeHtml(p.nombre)}</h4>
                <div class="seg-body">
                  ${listaHtml || '<p>Sin descripción</p>'}
                </div>
                <div class="seg-footer">
                  <div class="precio">$${Number(p.precio_por_dia).toFixed(2)} <small>MXN/día</small></div>
                  <button class="btn primary selectProteccion" type="button">Seleccionar</button>
                </div>
              </div>
            `;
          }).join("")}
        </div>
      </div>
    `;

    initDeclineLogic();

  } catch (err) {
    console.error("Error cargando protecciones:", err);
    list.innerHTML = '<div class="loading">Error al cargar datos.</div>';
  }
}

function initDeclineLogic() {
  const btnDecline = document.getElementById("btnDeclineModal");
  const modalDecline = document.getElementById("modalDeclineTerms");
  const btnConfirmar = document.getElementById("btnConfirmarDecline");
  const btnCancelar = document.getElementById("btnCerrarDeclineTerms");

  if (!btnDecline) return;

  btnDecline.onclick = () => {
    if (modalDecline) modalDecline.style.display = "flex";
  };

  if (btnCancelar) {
    btnCancelar.onclick = () => {
      if (modalDecline) modalDecline.style.display = "none";
    };
  }

  if (btnConfirmar) {
    btnConfirmar.onclick = () => {
      setProteccion({
        id: "decline_0",
        nombre: "DECLINE PROTECTIONS",
        precio: 0
      });

      if (modalDecline) modalDecline.style.display = "none";

      const mainModal = document.getElementById("proteccionPop");
      if (mainModal) {
        mainModal.classList.remove("active");
        mainModal.style.display = "none";
      }
    };
  }
}

function setProteccion(p) {
  if (typeof state === 'undefined') window.state = {};
  state.proteccion = p;

  const hid = document.getElementById("proteccion_id");
  if (hid) hid.value = p ? String(p.id) : "";

  const txt = document.getElementById("proteSelTxt");
  const sub = document.getElementById("proteSelSub");
  const rem = document.getElementById("proteRemove");

  if (!p) {
    if (txt) txt.textContent = "— Ninguna protección —";
    if (sub) sub.textContent = "Costo se refleja en el resumen.";
    if (rem) rem.style.display = "none";
    if (typeof actualizarTotal === 'function') actualizarTotal();
    return;
  }

  if (txt) txt.textContent = p.nombre;
  if (sub) {
    sub.textContent = p.precio > 0
      ? (typeof money === 'function' ? money(p.precio) : `$${Number(p.precio).toFixed(2)}`) + " / día"
      : "$0.00 MXN / día";
  }

  if (rem) rem.style.display = "";
  if (typeof actualizarTotal === 'function') actualizarTotal();
}

document.addEventListener('click', (e) => {
  if (e.target.classList && e.target.classList.contains('selectProteccion')) {
    const card = e.target.closest('.seg-card');
    if (card) {
      setProteccion({
        id: card.dataset.id,
        nombre: card.dataset.nombre,
        precio: card.dataset.precio
      });
      const mainModal = document.getElementById("proteccionPop");
      if (mainModal) {
        mainModal.classList.remove("active");
        mainModal.style.display = "none";
      }
    }
  }
});

/* ==========================================================
   9️⃣ ADICIONALES
========================================================== */
async function cargarAddons() {
  const list = document.getElementById("addonsList");
  if (!list) return;

  list.innerHTML = '<div class="loading">Cargando adicionales...</div>';

  try {
    const res = await fetch("/admin/cotizaciones/servicios");
    const data = await res.json();

    if (!data.length) {
      list.innerHTML = '<div class="loading">No hay adicionales disponibles.</div>';
      return;
    }

    list.innerHTML = data.map(add => `
      <article class="card-addon" data-id="${add.id_servicio}" data-nombre="${escapeHtml(add.nombre)}" data-precio="${add.precio}">
        <div class="ad-left">
          <div class="cp-title">${escapeHtml(add.nombre)}</div>
          <div class="cp-sub">${escapeHtml(add.descripcion || "")}</div>
        </div>
        <div class="ad-right">
          <div class="cp-price">
            <div class="muted small">Costo</div>
            <div class="price-big">$${Number(add.precio).toFixed(2)} <span>MXN/día</span></div>
          </div>
          <div class="qty-row">
            <button class="qty-btn minus" type="button">−</button>
            <div class="qty" data-qty>0</div>
            <button class="qty-btn plus" type="button">+</button>
          </div>
        </div>
      </article>
    `).join("");

  } catch (err) {
    console.error("Error cargando adicionales:", err);
    list.innerHTML = '<div class="loading">Error cargando adicionales.</div>';
  }
}

function setAddonQty(item, qty) {
  const q = Math.max(0, Number(qty || 0));
  if (q <= 0) state.addons.delete(String(item.id));
  else state.addons.set(String(item.id), { ...item, qty: q });

  syncAddonsHidden();
  refreshAddonsBadge();
  actualizarTotal();
}

function syncAddonsHidden() {
  const wrap = document.getElementById("extrasHidden");
  if (!wrap) return;

  wrap.innerHTML = "";
  let i = 0;
  state.addons.forEach((it) => {
    const qty = Number(it.qty || 0);
    if (qty <= 0) return;

    const fields = [
      ["id", it.id],
      ["cantidad", qty],
      ["precio", Number(it.precio || 0)],
      ["nombre", it.nombre || ""],
    ];

    fields.forEach(([k, v]) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = `adicionalesSeleccionados[${i}][${k}]`;
      input.value = String(v ?? "");
      wrap.appendChild(input);
    });
    i++;
  });
}

function refreshAddonsBadge() {
  const txt = document.getElementById("addonsSelTxt");
  const sub = document.getElementById("addonsSelSub");
  const clear = document.getElementById("addonsClear");

  const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);

  if (!items.length) {
    if (txt) txt.textContent = "— Ninguno —";
    if (sub) sub.textContent = "Subtotal estimado aparecerá aquí.";
    if (clear) clear.style.display = "none";
    return;
  }

  const names = items.slice(0, 2).map(x => `${x.nombre} ×${x.qty}`);
  const rest = items.length > 2 ? ` +${items.length - 2} más` : "";
  if (txt) txt.textContent = names.join(", ") + rest;

  const extrasSub = calcExtrasSubtotal();
  if (sub) sub.textContent = `Subtotal extras: ${money(extrasSub)}`;
  if (clear) clear.style.display = "";
}

function calcExtrasSubtotal() {
  let sum = 0;
  state.addons.forEach((it) => {
    const price = Number(it.precio || 0);
    const qty = Number(it.qty || 0);
    sum += price * qty * state.days;
  });
  return sum;
}

/* ==========================================================
   1️⃣0️⃣ TOTALES Y RESUMEN
========================================================== */
function actualizarTotal() {
  const days = state.days;
  const baseDia = state.categoria ? Number(state.categoria.precio_dia || 0) : 0;
  const baseTotal = baseDia * days;

  const protPrice = state.proteccion ? Number(state.proteccion.precio || 0) : 0;
  const protTotal = protPrice * days;

  const extrasTotal = calcExtrasSubtotal();

  const subtotal = baseTotal + protTotal + extrasTotal;
  const iva = subtotal * 0.16;
  const total = subtotal + iva;

  const moneda = document.getElementById("moneda")?.value || "MXN";
  let tc = parseFloat(document.getElementById("tc")?.value);
  if (isNaN(tc) || tc <= 0) tc = 17;
  const conv = moneda === "USD" ? (1 / tc) : 1;

  const resBaseDia = document.getElementById("resBaseDia");
  const resBaseTotal = document.getElementById("resBaseTotal");
  const resProte = document.getElementById("resProte");
  const resAdds = document.getElementById("resAdds");
  const resSub = document.getElementById("resSub");
  const resIva = document.getElementById("resIva");
  const resTotal = document.getElementById("resTotal");

  if (resBaseDia) resBaseDia.innerHTML = state.categoria ? `${money(baseDia)} / día` : "—";
  if (resBaseTotal) resBaseTotal.textContent = state.categoria ? money(baseTotal) : "—";
  if (resProte) resProte.textContent = state.proteccion ? `${state.proteccion.nombre} (${money(protPrice)}/día)` : "—";

  const items = Array.from(state.addons.values()).filter(x => Number(x.qty || 0) > 0);
  if (resAdds) resAdds.textContent = items.length ? items.map(x => `${x.nombre} ×${x.qty}`).join(", ") : "—";

  if (resSub) resSub.textContent = `${money(subtotal * conv)} ${moneda}`;
  if (resIva) resIva.textContent = `${money(iva * conv)} ${moneda}`;
  if (resTotal) resTotal.textContent = `${money(total * conv)} ${moneda}`;

  const tarifaBase = document.getElementById("tarifa_base");
  const tarifaModificada = document.getElementById("tarifa_modificada");
  const tarifaAjustada = document.getElementById("tarifa_ajustada");

  if (tarifaBase) tarifaBase.value = baseDia;
  if (tarifaModificada) tarifaModificada.value = state.tarifaEditada ? baseDia : baseDia;
  if (tarifaAjustada) tarifaAjustada.value = state.tarifaEditada ? "1" : "0";
}

function actualizarResumenViaje() {
  const sucRetiro = document.getElementById("sucursal_retiro");
  const sucEntrega = document.getElementById("sucursal_entrega");
  const fechaInicioUi = document.getElementById("fecha_inicio_ui");
  const horaRetiroUi = document.getElementById("hora_retiro_ui");
  const fechaFinUi = document.getElementById("fecha_fin_ui");
  const horaEntregaUi = document.getElementById("hora_entrega_ui");

  const resSucRetiro = document.getElementById("resSucursalRetiro");
  const resSucEntrega = document.getElementById("resSucursalEntrega");
  const resFechaInicio = document.getElementById("resFechaInicio");
  const resHoraInicio = document.getElementById("resHoraInicio");
  const resFechaFin = document.getElementById("resFechaFin");
  const resHoraFin = document.getElementById("resHoraFin");
  const resDias = document.getElementById("resDias");

  if (resSucRetiro) resSucRetiro.textContent = sucRetiro?.options[sucRetiro.selectedIndex]?.text || "—";
  if (resSucEntrega) resSucEntrega.textContent = sucEntrega?.options[sucEntrega.selectedIndex]?.text || "—";
  if (resFechaInicio) resFechaInicio.textContent = fechaInicioUi?.value || "—";
  if (resHoraInicio) resHoraInicio.textContent = horaRetiroUi?.value || "—";
  if (resFechaFin) resFechaFin.textContent = fechaFinUi?.value || "—";
  if (resHoraFin) resHoraFin.textContent = horaEntregaUi?.value || "—";
  if (resDias) resDias.textContent = `${state.days || 0} día(s)`;
}

/* ==========================================================
   1️⃣1️⃣ ENVÍO DE COTIZACIÓN
========================================================== */
async function enviarCotizacion(data, accion) {
  try {
    const res = await fetch("/admin/cotizaciones/guardar", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(data),
    });

    const result = await res.json();

    if (res.ok && result.success) {
      alertify.success(`✅ Cotización ${accion} correctamente`);

      if (accion.includes("confirmada")) {
        setTimeout(() => (window.location.href = "/admin/reservaciones-activas"), 1500);
      } else {
        const confirmPop = document.getElementById("confirmPop");
        if (confirmPop) openPop(confirmPop);
        setTimeout(() => window.location.reload(), 2000);
      }
    } else {
      alertify.error(`⚠️ Error al ${accion} cotización`);
    }
  } catch (err) {
    console.error(err);
    alertify.error("Error de conexión con el servidor");
  }
}

function obtenerDatosCotizacion() {
  return {
    categoria_id: document.getElementById("categoriaSelect")?.value,
    tarifa_base: state.categoria?.precio_dia || 0,
    pickup_sucursal_id: document.getElementById("sucursal_retiro")?.value,
    dropoff_sucursal_id: document.getElementById("sucursal_entrega")?.value,
    pickup_date: document.getElementById("fecha_inicio")?.value,
    pickup_time: document.getElementById("hora_retiro")?.value,
    dropoff_date: document.getElementById("fecha_fin")?.value,
    dropoff_time: document.getElementById("hora_entrega")?.value,
    days: state.days,
    seguro: state.proteccion,
    extras: Array.from(state.addons.values()),
    moneda: document.getElementById("moneda")?.value,
    tipo_cambio: parseFloat(document.getElementById("tc")?.value || 17),
    cliente: {
      nombre: document.getElementById("nombre_cliente")?.value,
      apellidos: document.getElementById("apellidos")?.value,
      email: document.getElementById("email_cliente")?.value,
      telefono: document.getElementById("telefono_cliente")?.value,
      pais: document.getElementById("pais")?.value,
      vuelo: document.getElementById("no_vuelo")?.value,
    },
  };
}

/* ==========================================================
   1️⃣2️⃣ FUNCIONES DE VALIDACIÓN CON ALERTAS TOAST
========================================================== */
function validarSucursales() {
  const sucRetiro = document.getElementById("sucursal_retiro")?.value;
  const sucEntrega = document.getElementById("sucursal_entrega")?.value;

  if (!sucRetiro || !sucEntrega) {
    mostrarToast('⚠️ Primero debes seleccionar las sucursales de RETIRO y ENTREGA', 'warning');
    return false;
  }
  return true;
}

function validarFechasHoras() {
  if (!validarSucursales()) return false;

  const fechaInicio = document.getElementById("fecha_inicio")?.value;
  const fechaFin = document.getElementById("fecha_fin")?.value;
  const horaRetiro = document.getElementById("hora_retiro")?.value;
  const horaEntrega = document.getElementById("hora_entrega")?.value;

  if (!fechaInicio || !fechaFin || !horaRetiro || !horaEntrega) {
    mostrarToast('⚠️ Completa FECHA y HORA de salida y llegada', 'warning');
    return false;
  }
  return true;
}

function validarCategoria() {
  if (!validarFechasHoras()) return false;

  if (!state.categoria) {
    mostrarToast('⚠️ Selecciona una CATEGORÍA de vehículo', 'warning');
    return false;
  }
  return true;
}

/* ==========================================================
   1️⃣3️⃣ EVENTOS Y MODALES
========================================================== */
function bindUI() {
  const btnCategorias = document.getElementById("btnCategorias");
  const catPop = document.getElementById("catPop");
  const catClose = document.getElementById("catClose");
  const catCancel = document.getElementById("catCancel");
  const categoriasGrid = document.getElementById("categoriasGrid");
  const catRemove = document.getElementById("catRemove");

  if (btnCategorias) {
    btnCategorias.addEventListener("click", async () => {
      if (!validarFechasHoras()) return;

      await cargarCategorias();
      repaintCategoriaModalEstimados();
      if (catPop) openPop(catPop);
    });
  }

  if (catClose) catClose.addEventListener("click", () => closePop(catPop));
  if (catCancel) catCancel.addEventListener("click", () => closePop(catPop));

  if (categoriasGrid) {
    categoriasGrid.addEventListener("click", (e) => {
      const card = e.target.closest(".card-pick");
      if (!card) return;

      setCategoria({
        id: card.dataset.id,
        nombre: card.dataset.nombre,
        desc: card.dataset.desc,
        precio_dia: Number(card.dataset.precio),
      });
      closePop(catPop);
    });
  }

  if (catRemove) catRemove.addEventListener("click", () => setCategoria(null));

  const btnProtecciones = document.getElementById("btnProtecciones");
  const protePop = document.getElementById("proteccionPop");
  const proteClose = document.getElementById("proteClose");
  const proteCancel = document.getElementById("proteCancel");
  const proteList = document.getElementById("proteList");
  const proteRemove = document.getElementById("proteRemove");

  if (btnProtecciones) {
    btnProtecciones.addEventListener("click", async () => {
      await cargarProtecciones();
      if (protePop) openPop(protePop);
    });
  }

  if (proteClose) proteClose.addEventListener("click", () => closePop(protePop));
  if (proteCancel) proteCancel.addEventListener("click", () => closePop(protePop));

  if (proteList) {
    proteList.addEventListener("click", (e) => {
      const btn = e.target.closest(".selectProteccion");
      if (!btn) return;
      const card = btn.closest(".seg-card");

      setProteccion({
        id: card.dataset.id,
        nombre: card.dataset.nombre,
        precio: Number(card.dataset.precio),
      });
      closePop(protePop);
    });
  }

  if (proteRemove) proteRemove.addEventListener("click", () => setProteccion(null));

  const btnAddons = document.getElementById("btnAddons");
  const addonsPop = document.getElementById("addonsPop");
  const addonsClose = document.getElementById("addonsClose");
  const addonsCancel = document.getElementById("addonsCancel");
  const addonsApply = document.getElementById("addonsApply");
  const addonsClear = document.getElementById("addonsClear");
  const addonsList = document.getElementById("addonsList");

  if (btnAddons) {
    btnAddons.addEventListener("click", async () => {
      await cargarAddons();
      if (addonsPop) openPop(addonsPop);
    });
  }

  if (addonsClose) addonsClose.addEventListener("click", () => closePop(addonsPop));
  if (addonsCancel) addonsCancel.addEventListener("click", () => closePop(addonsPop));
  if (addonsApply) {
    addonsApply.addEventListener("click", () => {
      closePop(addonsPop);
      refreshAddonsBadge();
      actualizarTotal();
    });
  }
  if (addonsClear) {
    addonsClear.addEventListener("click", () => {
      state.addons.clear();
      syncAddonsHidden();
      refreshAddonsBadge();
      actualizarTotal();
    });
  }

  if (addonsList) {
    addonsList.addEventListener("click", (e) => {
      const card = e.target.closest(".card-addon");
      if (!card) return;

      const plus = e.target.closest(".plus");
      const minus = e.target.closest(".minus");
      if (!plus && !minus) return;

      const item = {
        id: card.dataset.id,
        nombre: card.dataset.nombre,
        precio: Number(card.dataset.precio),
      };
      const cur = state.addons.get(String(item.id))?.qty || 0;
      const next = Math.max(0, Number(cur) + (plus ? 1 : -1));

      setAddonQty(item, next);
      const qtyEl = card.querySelector("[data-qty]");
      if (qtyEl) qtyEl.textContent = next;
    });
  }

  const btnResumen = document.getElementById("btnResumen");
  const resumenPop = document.getElementById("resumenPop");
  const resumenClose = document.getElementById("resumenClose");
  const resumenOk = document.getElementById("resumenOk");

  if (btnResumen) {
    btnResumen.addEventListener("click", () => {
      actualizarResumenViaje();
      actualizarTotal();
      if (resumenPop) openPop(resumenPop);
    });
  }

  if (resumenClose) resumenClose.addEventListener("click", () => closePop(resumenPop));
  if (resumenOk) resumenOk.addEventListener("click", () => closePop(resumenPop));

  const btnEditarTarifa = document.getElementById("btnEditarTarifa");
  if (btnEditarTarifa) {
    btnEditarTarifa.addEventListener("click", () => {
      if (!state.categoria) return;

      const container = document.getElementById("resBaseDia");
      if (!container || container.querySelector("input")) return;

      const input = document.createElement("input");
      input.type = "number";
      input.value = state.categoria.precio_dia.toFixed(2);
      input.style.width = "90px";
      input.style.padding = "4px";
      input.style.border = "1px solid #2563eb";
      input.style.borderRadius = "6px";

      container.innerHTML = "";
      container.appendChild(input);
      input.focus();

      input.addEventListener("blur", () => {
        const nuevo = parseFloat(input.value);
        if (!isNaN(nuevo) && nuevo > 0) {
          state.categoria.precio_dia = nuevo;
          state.tarifaEditada = true;
          actualizarTotal();
          refreshCategoriaPreview();
        }
        container.innerHTML = `${money(state.categoria.precio_dia)} / día`;
      });
    });
  }

  const btnGuardar = document.getElementById("btnGuardarYEnviar");
  const btnConfirmar = document.getElementById("btnConfirmarCotizacion");

  if (btnGuardar) {
    btnGuardar.addEventListener("click", async () => {
      if (!validarCategoria()) return;
      if (!document.getElementById("nombre_cliente")?.value) {
        mostrarToast('⚠️ Completa los datos del cliente', 'warning');
        return;
      }

      const payload = obtenerDatosCotizacion();
      payload.enviarCorreo = true;
      await enviarCotizacion(payload, "guardada");
    });
  }

  if (btnConfirmar) {
    btnConfirmar.addEventListener("click", async () => {
      if (!validarCategoria()) return;
      if (!document.getElementById("nombre_cliente")?.value) {
        mostrarToast('⚠️ Completa los datos del cliente', 'warning');
        return;
      }

      const payload = obtenerDatosCotizacion();
      payload.confirmar = true;
      await enviarCotizacion(payload, "confirmada");
    });
  }

  const moneda = document.getElementById("moneda");
  const tc = document.getElementById("tc");
  if (moneda) moneda.addEventListener("change", actualizarTotal);
  if (tc) tc.addEventListener("input", actualizarTotal);

  const sucRetiro = document.getElementById("sucursal_retiro");
  const sucEntrega = document.getElementById("sucursal_entrega");
  if (sucRetiro) sucRetiro.addEventListener("change", actualizarResumenViaje);
  if (sucEntrega) sucEntrega.addEventListener("change", actualizarResumenViaje);

  qsa(".pop.modal").forEach((pop) => {
    pop.addEventListener("click", (e) => {
      if (e.target === pop) closePop(pop);
    });
  });
}

/* ==========================================================
   1️⃣4️⃣ SELECT2 CON ICONOS
========================================================== */
window.iconosPorId = window.iconosPorId || {};

function initSelect2Sucursales() {
  if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
    console.warn('Select2 no está disponible');
    return;
  }

  console.log('Inicializando Select2 con iconos negros...');

  function formatOption(option) {
    if (!option.id) {
      return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
    }

    let iconClass = window.iconosPorId[option.id] || 'fa-building';
    return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
  }

  function formatSelection(option) {
    if (!option.id) {
      return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
    }

    let iconClass = window.iconosPorId[option.id] || 'fa-building';
    return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right: 10px; color: #000000; font-size: 18px;"></i> ' + option.text + '</span>');
  }

  if ($('#sucursal_retiro').data('select2')) {
    $('#sucursal_retiro').select2('destroy');
  }
  if ($('#sucursal_entrega').data('select2')) {
    $('#sucursal_entrega').select2('destroy');
  }

  const select2Config = {
    templateResult: formatOption,
    templateSelection: formatSelection,
    escapeMarkup: function(markup) { return markup; },
    width: '100%',
    minimumResultsForSearch: Infinity,
    allowClear: false
  };

  $('#sucursal_retiro').select2(select2Config);
  $('#sucursal_entrega').select2(select2Config);

  console.log('✅ Select2 inicializado correctamente');
}

/* ==========================================================
   1️⃣5️⃣ INICIALIZACIÓN PRINCIPAL
========================================================== */
document.addEventListener("DOMContentLoaded", () => {
  initFlatpickrModal();
  calcularDias();
  actualizarResumenViaje();
  bindUI();
});

$(document).ready(function() {
  setTimeout(initSelect2Sucursales, 300);
});
