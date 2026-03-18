// public/js/politicas.js
(function () {
  "use strict";

  const AUTH_KEY = "vj_auth";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
    } else {
      fn();
    }
  }

  // Helpers
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  function safeJsonParse(str) {
    try { return JSON.parse(str); } catch (_) { return null; }
  }

  if (!window.VJ_AUTH) {
    function getAuth() {
      return safeJsonParse(localStorage.getItem(AUTH_KEY) || "null");
    }
    function isLogged() {
      return !!localStorage.getItem(AUTH_KEY);
    }
    window.VJ_AUTH = { getAuth, isLogged };
  }

  // =========================
  //  Polyfills
  // =========================
  if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
      let el = this;
      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);
      return null;
    };
  }

  // =========================
  //  Función para inyectar CSS de selects de hora
  // =========================
  (function injectTimeCss() {
    const id = "tpHideInputStyle";
    if (document.getElementById(id)) return;
    const st = document.createElement("style");
    st.id = id;
    st.textContent = `
      .tp-hidden-input { display: none !important; }
      .tp-selects { display: flex; gap: 10px; margin-top: 10px; }
      .tp-selects select {
        width: 100%;
        height: 48px;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 0 8px 0 36px;
        font-size: 14px;
        color: #666;
        background: white;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
      }
      .tp-selects select:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(178,34,34,.15);
      }
      .time-field { position: relative; }
      .time-field::before {
        content: "\\f017";
        font-family: "Font Awesome 6 Free";
        font-weight: 400;
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        font-size: 16px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        pointer-events: none;
      }
      .time-field .field-icon { display: none !important; }
    `;
    document.head.appendChild(st);
  })();

  // =========================
  //  SELECTS DE HORA
  // =========================
  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  function createTimeSelectsBelow(input, opts) {
    const { hourMax = 24, defaultValue = "12:00" } = (opts || {});

    // Usar traducciones de window.politicasTranslations
    const translations = window.politicasTranslations || {
        hora: "Hora"  // Valor por defecto
    };

    const wrap = input.closest(".time-field") || input.parentElement;
    if (wrap && wrap.querySelector(".tp-selects")) return;

    const box = document.createElement("div");
    box.className = "tp-selects w-100";

    const selH = document.createElement("select");
    selH.className = "tp-hour";
    selH.setAttribute("aria-label", translations.hora);

    // Placeholder con el texto traducido
    selH.insertAdjacentHTML("afterbegin", `<option value="" disabled selected>${translations.hora}</option>`);

    for (let h = 1; h <= hourMax; h++) {
        const op = document.createElement("option");
        op.value = String(h);
        op.textContent = pad2(h);
        selH.appendChild(op);
    }

    function sync() {
        const finalH = pad2(Number(selH.value || 0));
        input.value = `${finalH}:00`;
        input.dispatchEvent(new Event("input", { bubbles: true }));
        input.dispatchEvent(new Event("change", { bubbles: true }));
    }

    selH.addEventListener("change", sync);
    box.appendChild(selH);

    if (wrap) {
        wrap.appendChild(box);
    } else {
        input.insertAdjacentElement("afterend", box);
    }

    if (input.value && input.value !== "12:00") {
        const defaultHour = input.value.split(':')[0];
        const option = Array.from(selH.options).find(opt => opt.value === defaultHour);
        if (option) {
            option.selected = true;
            sync();
        }
    } else {
        selH.selectedIndex = 0;
        input.value = "";
    }
}
  function initAnalogTime(id) {
    const input = document.getElementById(id);
    if (!input) return;
    if (input.dataset.tpReady === "1") return;
    input.dataset.tpReady = "1";

    input.setAttribute("readonly", "readonly");
    input.setAttribute("inputmode", "none");
    input.classList.add("tp-hidden-input");
    input.setAttribute("aria-hidden", "true");

    createTimeSelectsBelow(input, {
      hourMax: 24,
      defaultValue: input.value || "12:00"
    });
  }

  // =========================
  //  FUNCIÓN PARA FECHAS POR DEFECTO (AHORA VACÍO)
  // =========================
  function setDefaultDates() {
    const pickupTime = document.getElementById('pickupTimePoliticas');
    const dropoffTime = document.getElementById('dropoffTimePoliticas');

    if (pickupTime && !pickupTime.value) pickupTime.value = "10:00";
    if (dropoffTime && !dropoffTime.value) dropoffTime.value = "10:00";

    console.log('Fechas configuradas: sin valores por defecto');
  }
// =========================
//  SELECT2 CON ICONOS - VERSIÓN CON TEXTOS DINÁMICOS
// =========================
function setupSelect2Iconos() {
  if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
    console.warn(' Select2 no está disponible');
    return;
  }

  console.log(' Inicializando Select2 con iconos...');

  // Función ÚNICA de formato (definida una sola vez)
  function formatOption(option) {
    if (!option.id) {
      return $('<span><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
    }

    let iconClass = 'fa-building';
    const text = option.text.toLowerCase();

    if (text.includes('aeropuerto') || text.includes('airport')) {
      iconClass = 'fa-plane-departure';
    } else if (text.includes('central de autobuses') || text.includes('terminal') || text.includes('bus station')) {
      iconClass = 'fa-bus';
    }

    return $('<span><i class="fa-solid ' + iconClass + '" style="margin-right: 8px; color: #333;"></i> ' + option.text + '</span>');
  }

  const modal = document.getElementById('miBuscadorPoliticas');
  const dropoffWrapper = document.getElementById('dropoffWrapperPoliticas');
  const dropoffSelect = document.getElementById('dropoffPlacePoliticas');

  // Guardar estado original del dropoff
  let originalDisplay = dropoffWrapper ? dropoffWrapper.style.display : null;
  let originalDisabled = dropoffSelect ? dropoffSelect.disabled : null;

  // Temporalmente habilitar dropoff para inicialización
  if (dropoffWrapper && dropoffSelect) {
    dropoffWrapper.style.display = 'block';
    dropoffSelect.disabled = false;
  }

  // OBTENER TEXTOS ACTUALIZADOS DEL HTML
  const pickupPlaceholder = document.querySelector('#pickupPlacePoliticas option[disabled][selected]')?.textContent || '¿Dónde inicia tu viaje?';
  const dropoffPlaceholder = document.querySelector('#dropoffPlacePoliticas option[disabled][selected]')?.textContent || '¿Dónde termina tu viaje?';

  // Configuración base para Select2 - CON PLACEHOLDERS DINÁMICOS
  const select2Config = {
    templateResult: formatOption,
    templateSelection: formatOption,
    escapeMarkup: function(m) { return m; },
    width: '100%',
    minimumResultsForSearch: Infinity,
    allowClear: false,
    dropdownParent: modal ? $(modal) : undefined
  };

  // Destruir instancias existentes
  try {
    ['#pickupPlacePoliticas', '#dropoffPlacePoliticas'].forEach(selector => {
      if ($(selector).data('select2')) {
        $(selector).select2('destroy');
      }
    });
  } catch(e) {
    console.log('Error destruyendo instancias previas:', e);
  }

  // Inicializar pickup con placeholder dinámico
  $('#pickupPlacePoliticas').select2({
    ...select2Config,
    placeholder: pickupPlaceholder
  });

  // Inicializar dropoff con placeholder dinámico
  $('#dropoffPlacePoliticas').select2({
    ...select2Config,
    placeholder: dropoffPlaceholder
  });

  // Restaurar estado original del dropoff
  setTimeout(() => {
    if (dropoffWrapper && dropoffSelect) {
      dropoffWrapper.style.display = originalDisplay === 'none' ? 'none' : originalDisplay;
      dropoffSelect.disabled = originalDisabled;

      // Si estaba deshabilitado, actualizar Select2
      if (originalDisabled) {
        $('#dropoffPlacePoliticas').prop('disabled', true);
      }
    }
  }, 100);

  console.log(' Select2 inicializado con placeholders:', pickupPlaceholder, dropoffPlaceholder);
}
// =========================
//  DETECTOR DE CAMBIO DE IDIOMA
// =========================
function detectLanguageChange() {
  // Guardar el idioma actual
  let currentLang = document.documentElement.lang || 'es';

  // Observar cambios en el atributo lang del HTML
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.attributeName === 'lang') {
        const newLang = document.documentElement.lang;
        console.log('Idioma detectado:', newLang);

        // Re-inicializar componentes que necesitan actualizarse
        setTimeout(() => {
          // Destruir y recrear Select2 con los nuevos textos
          if (typeof $ !== 'undefined' && $.fn.select2) {
            try {
              $('#pickupPlacePoliticas').select2('destroy');
              $('#dropoffPlacePoliticas').select2('destroy');
            } catch(e) {}

            // Pequeño retraso para asegurar que el DOM se actualizó
            setTimeout(setupSelect2Iconos, 100);
          }
        }, 50);
      }
    });
  });

  // Iniciar observación
  observer.observe(document.documentElement, { attributes: true });

  // También verificar después de cada clic en cambio de idioma
  document.querySelectorAll('[href*="lang/"]').forEach(link => {
    link.addEventListener('click', function() {
      // Permitir que la página recargue pero marcar que necesitamos reinicializar
      sessionStorage.setItem('languageJustChanged', 'true');
    });
  });

  // Verificar si venimos de un cambio de idioma
  if (sessionStorage.getItem('languageJustChanged') === 'true') {
    sessionStorage.removeItem('languageJustChanged');
    setTimeout(() => {
      if (typeof $ !== 'undefined' && $.fn.select2) {
        try {
          $('#pickupPlacePoliticas').select2('destroy');
          $('#dropoffPlacePoliticas').select2('destroy');
        } catch(e) {}
        setTimeout(setupSelect2Iconos, 100);
      }
    }, 150);
  }
}
  // =========================
  //  FUNCIÓN PARA CAMBIAR ICONOS SEGÚN SELECCIÓN
  // =========================
  function setupIconosDinamicos() {
    const pickupSelect = document.getElementById('pickupPlacePoliticas');
    const dropoffSelect = document.getElementById('dropoffPlacePoliticas');
    const pickupIcon = document.getElementById('pickupIcon');
    const dropoffIcon = document.getElementById('dropoffIcon');

    function getIconClass(text) {
      const textoLower = text.toLowerCase();
      if (textoLower.includes('aeropuerto')) {
        return 'fa-plane-departure';
      } else if (textoLower.includes('central') || textoLower.includes('terminal')) {
        return 'fa-bus';
      }
      return 'fa-building';
    }

    function updateIcon(select, iconElement) {
      if (!select || !iconElement) return;

      if (select.value && select.value !== '') {
        const selectedOption = select.options[select.selectedIndex];
        const iconClass = getIconClass(selectedOption.text);
        iconElement.innerHTML = `<i class="fa-solid ${iconClass}"></i>`;
      } else {
        iconElement.innerHTML = '<i class="fa-solid fa-location-dot"></i>';
      }
    }

    if (pickupSelect && pickupIcon) {
      updateIcon(pickupSelect, pickupIcon);
      pickupSelect.addEventListener('change', () => updateIcon(pickupSelect, pickupIcon));
    }

    if (dropoffSelect && dropoffIcon) {
      updateIcon(dropoffSelect, dropoffIcon);
      dropoffSelect.addEventListener('change', () => updateIcon(dropoffSelect, dropoffIcon));
    }
  }

  // =========================
//  CHECKBOX - CONTROL DROPOFF (VERSIÓN MEJORADA)
// =========================
function setupCheckbox() {
  const chk = document.getElementById('differentDropoffPoliticas');
  const dropWrap = document.getElementById('dropoffWrapperPoliticas');
  const pickSel = document.getElementById('pickupPlacePoliticas');
  const dropSel = document.getElementById('dropoffPlacePoliticas');

  if (!chk || !dropWrap) return;

  function updateDropoffState() {
    const isChecked = chk.checked;

    dropWrap.style.display = isChecked ? 'block' : 'none';

    if (dropSel) {
      dropSel.disabled = !isChecked;
      dropSel.required = isChecked;

      // Actualizar Select2
      if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#dropoffPlacePoliticas').prop('disabled', !isChecked).trigger('change');
      }

      // Si está deshabilitado, copiar valor de pickup
      if (!isChecked && pickSel && pickSel.value) {
        dropSel.value = pickSel.value;
        if (typeof $ !== 'undefined' && $.fn.select2) {
          $('#dropoffPlacePoliticas').val(pickSel.value).trigger('change');
        }
      }
    }
  }

  // Estado inicial
  updateDropoffState();

  // Escuchar cambios
  chk.addEventListener('change', updateDropoffState);

  // Si pickup cambia y dropoff está deshabilitado, actualizar
  if (pickSel && dropSel) {
    pickSel.addEventListener('change', function() {
      if (!chk.checked) {
        dropSel.value = this.value;
        if (typeof $ !== 'undefined' && $.fn.select2) {
          $('#dropoffPlacePoliticas').val(this.value).trigger('change');
        }
      }
    });
  }
}
  // =========================
  //  ERRORES
  // =========================
  function showError(element, message) {
    if (!element) return;

    element.classList.remove('field-success');
    element.classList.remove('field-error');
    const oldError = element.parentElement?.querySelector('.error-msg');
    if (oldError) oldError.remove();

    element.classList.add('field-error');

    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(element).next('.select2-container').find('.select2-selection')
            .removeClass('field-success')
            .addClass('field-error');
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    element.parentElement?.appendChild(errorDiv);

    console.log('Error mostrado:', message, 'en', element);
  }

 // =========================
//  VALIDACIÓN DEL FORMULARIO - POLÍTICAS
// =========================
function setupValidation() {
    const form = document.getElementById('rentalFormPoliticas');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('=== VALIDANDO FORMULARIO POLÍTICAS ===');

        let valid = true;

        form.querySelectorAll('.error-msg').forEach(el => el.remove());
        form.querySelectorAll('.field-error, .field-success').forEach(el => {
            el.classList.remove('field-error', 'field-success');
        });

        if (typeof $ !== 'undefined' && $.fn.select2) {
            form.querySelectorAll('.select2-selection').forEach(el => {
                el.classList.remove('field-error', 'field-success');
            });
        }

        const checkbox = document.getElementById('differentDropoffPoliticas');
        const translations = window.politicasTranslations || {};

        const selects = [
            { id: 'pickupPlacePoliticas', msg: translations.ubicacion_requerida || 'Ubicación Requerida' }
        ];

        // solo exigir dropoff si el checkbox está activado
        if (checkbox && checkbox.checked) {
            selects.push({ id: 'dropoffPlacePoliticas', msg: translations.ubicacion_requerida || 'Ubicación Requerida' });
        }
        selects.forEach(campo => {
            const select = document.getElementById(campo.id);
            if (!select) return;

            const container = select.closest('.field');

            if (!select.value) {
                valid = false;
                select.classList.add('field-error');

                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(select).next('.select2-container')
                        .find('.select2-selection')
                        .addClass('field-error');
                }

                if (container) {
                    const msg = document.createElement('span');
                    msg.className = 'error-msg';
                    msg.textContent = campo.msg;
                    container.appendChild(msg);
                }
            } else {
                select.classList.add('field-success');

                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(select).next('.select2-container')
                        .find('.select2-selection')
                        .addClass('field-success');
                }
            }
        });

        // VALIDAR FECHAS (FLATPICKR)
        const fechas = [
            { id: 'pickupDatePoliticas', msg: translations.fecha_requerida || 'Fecha Requerida' },
            { id: 'dropoffDatePoliticas', msg: translations.fecha_requerida || 'Fecha Requerida' }
        ];

        fechas.forEach(campo => {
            const hiddenInput = document.getElementById(campo.id);
            if (!hiddenInput) return;

            const picker = hiddenInput._flatpickr;
            const altInput = picker ? picker.altInput : null;
            const container = hiddenInput.closest('.dt-field');
            const hasValue = hiddenInput.value && hiddenInput.value.trim() !== '';

            if (!hasValue) {
                valid = false;

                if (altInput) {
                    altInput.classList.add('field-error');
                    altInput.classList.remove('field-success');
                }

                if (container) {
                    const msg = document.createElement('span');
                    msg.className = 'error-msg';
                    msg.textContent = campo.msg;
                    container.appendChild(msg);
                }
            } else {
                if (altInput) {
                    altInput.classList.add('field-success');
                    altInput.classList.remove('field-error');
                }
            }
        });

        // VALIDAR HORAS
        const horas = [
            { id: 'pickupTimePoliticas', msg: translations.hora_requerida || 'Hora Requerida' },
            { id: 'dropoffTimePoliticas', msg: translations.hora_requerida || 'Hora Requerida' }
        ];

        horas.forEach(campo => {
            const hiddenInput = document.getElementById(campo.id);
            if (!hiddenInput) return;

            const timeField = hiddenInput.closest('.time-field');
            if (!timeField) return;

            const hourSelect = timeField.querySelector('.tp-selects .tp-hour');
            const hasValue = hourSelect && hourSelect.value;

            if (!hasValue) {
                valid = false;

                if (hourSelect) hourSelect.classList.add('field-error');
                hiddenInput.classList.add('field-error');

                const msg = document.createElement('span');
                msg.className = 'error-msg';
                msg.textContent = campo.msg;
                timeField.appendChild(msg);
            } else {
                if (hourSelect) hourSelect.classList.add('field-success');
                hiddenInput.classList.add('field-success');
            }
        });

        console.log('Resultado:', valid ? ' VÁLIDO' : ' INVÁLIDO');

        if (valid) {
            const pickup = document.getElementById('pickupPlacePoliticas');
            const dropoff = document.getElementById('dropoffPlacePoliticas');
            const checkbox = document.getElementById('differentDropoffPoliticas');

            if (checkbox && !checkbox.checked) {
                dropoff.value = pickup.value;
            }
            form.submit();
        }
    });
}



  // =========================
  //  MODAL
  // =========================
  function setupPolicyModal() {
    const modal = qs("#policyModal");
    const modalBody = qs("#policyModalBody");
    const modalTitle = qs("#policyModalTitle");
    if (!modal || !modalBody || !modalTitle) return;

    let lastFocus = null;

    function openModal(title, tplId) {
      const tpl = qs(`#${tplId}`);
      if (!tpl) return;
      lastFocus = document.activeElement;
      modalTitle.textContent = title || "Política";
      modalBody.innerHTML = tpl.innerHTML;
      modal.classList.add("open");
      document.body.classList.add("modal-open");
    }

    function closeModal() {
      modal.classList.remove("open");
      document.body.classList.remove("modal-open");
      modalBody.innerHTML = "";
      if (lastFocus && typeof lastFocus.focus === "function") {
        lastFocus.focus();
      }
    }

    qsa(".policy-card").forEach((btn) => {
      btn.addEventListener("click", () => {
        const tplId = btn.getAttribute("data-modal");
        const title = btn.getAttribute("data-title") || btn.textContent.trim();
        if (tplId) openModal(title, tplId);
      });
    });

    qsa('[data-close="1"]', modal).forEach((el) => el.addEventListener("click", closeModal));
    modal.addEventListener("click", (e) => {
      if (e.target.matches(".vj-modal__backdrop")) closeModal();
    });
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.classList.contains("open")) closeModal();
    });
  }

  // ========================
  //  NAVBAR
  // =========================
  function setupNavbar() {
    const topbar = qs("#topbar");
    function toggleTopbar() {
      if (topbar) {
        window.scrollY > 40 ? topbar.classList.add("solid") : topbar.classList.remove("solid");
      }
    }
    toggleTopbar();
    window.addEventListener("scroll", toggleTopbar, { passive: true });
  }

  // =========================
  //  ACCOUNT LINK
  // =========================
  function setupAccountLink() {
    const accountLink = qs("#accountLink");
    if (!accountLink) return;

    const logged = window.VJ_AUTH?.isLogged?.() || false;
    const auth = window.VJ_AUTH?.getAuth?.() || {};
    const loginUrl = accountLink.getAttribute("data-login-url") || "/login";
    const profileUrl = accountLink.getAttribute("data-profile-url") || "/perfil";

    if (logged) {
      accountLink.href = profileUrl;
      accountLink.title = "Mi perfil";
      const letter = (auth.name?.[0] || auth.email?.[0] || "U").toUpperCase();
      accountLink.innerHTML = `<span class="avatar-mini">${letter}</span>`;
    } else {
      accountLink.href = loginUrl;
      accountLink.title = "Iniciar sesión";
      accountLink.innerHTML = `<i class="fa-regular fa-user"></i>`;
    }
  }

  // =========================
  // FOOTER YEAR
  // =========================
  function setupFooterYear() {
    const yearEl = qs("#year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  }

  // ============================================================
  // CONTROL DE SCROLL PARA FORMULARIO POLÍTICAS
  // ============================================================
  function initBuscadorPoliticas() {
    const btnAbrir = document.getElementById('btn-abrir-buscador-politicas');
    const btnCerrar = document.getElementById('btn-cerrar-buscador-politicas');
    const buscador = document.getElementById('miBuscadorPoliticas');

    if (!btnAbrir || !btnCerrar || !buscador) {
      console.log('Elementos del buscador no encontrados');
      return;
    }

    function bloquearScroll() {
      const scrollY = window.scrollY;
      document.body.style.position = 'fixed';
      document.body.style.top = `-${scrollY}px`;
      document.body.style.left = '0';
      document.body.style.right = '0';
      document.body.style.overflow = 'hidden';
      document.body.style.width = '100%';
      document.body.dataset.scrollY = scrollY;
    }

    function restaurarScroll() {
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.left = '';
      document.body.style.right = '';
      document.body.style.overflow = '';
      document.body.style.width = '';
      const scrollY = document.body.dataset.scrollY || 0;
      window.scrollTo(0, parseInt(scrollY));
      delete document.body.dataset.scrollY;
    }

    btnAbrir.addEventListener('click', function(e) {
      e.preventDefault();
      buscador.classList.add('active');
      bloquearScroll();
    });

    btnCerrar.addEventListener('click', function(e) {
      e.preventDefault();
      buscador.classList.remove('active');
      restaurarScroll();
    });

    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && buscador.classList.contains('active')) {
        buscador.classList.remove('active');
        restaurarScroll();
      }
    });

    document.body.addEventListener('touchmove', function(e) {
      if (buscador.classList.contains('active')) {
        e.preventDefault();
      }
    }, { passive: false });
  }

  // =========================
  //  INICIALIZACIÓN PRINCIPAL
  // =========================
  onReady(() => {
  console.log('Inicializando políticas.js');

  setupNavbar();
  setupAccountLink();
  setupFooterYear();
  setupPolicyModal();

  initAnalogTime("pickupTimePoliticas");
  initAnalogTime("dropoffTimePoliticas");


  setDefaultDates();
  initBuscadorPoliticas();

  // Inicializar Select2
  setTimeout(() => {
    setupSelect2Iconos();
    setupIconosDinamicos();
    setupCheckbox();
    setupValidation();
  }, 300);

  // DETECTOR DE CAMBIO DE IDIOMA
  detectLanguageChange();

  console.log(' Formulario de políticas listo');
});
})();
