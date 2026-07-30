
(function (global) {
    'use strict';

    const CONFIG = {
        mensajes: [
            'Procesando tu información…',
            'Preparando los cambios…',
            'Generando tu actualización…'
        ],
        intervaloMensajes: 1500,
        retrasoEnvio: 800,
        tiempoMaximo: 12000,
        duracionExito: 1200,
        selectorExito: '.visor-alert.alert-success, .alert-success',
        claveSesion: 'vrUpdated'
    };

    const $ = (id) => document.getElementById(id);
    let temporizadorMsg = null;

    function rotarMensajes(lista) {
        const el = $('vrUpdateMsg');
        const mensajes = (lista && lista.length) ? lista : CONFIG.mensajes;
        let i = 0;
        if (el) el.textContent = mensajes[0];
        clearInterval(temporizadorMsg);
        temporizadorMsg = setInterval(() => {
            i = (i + 1) % mensajes.length;
            if (!el) return;
            el.textContent = mensajes[i];
            el.style.animation = 'none';
            void el.offsetWidth;
            el.style.animation = '';
        }, CONFIG.intervaloMensajes);
    }

    function mostrar(estado, opciones) {
        const overlay = $('vrUpdateOverlay');
        if (!overlay) return;

        opciones = opciones || {};

        overlay.classList.remove('is-loading', 'is-success');
        overlay.classList.add('show', estado === 'success' ? 'is-success' : 'is-loading');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (estado === 'success') {
            clearInterval(temporizadorMsg);
            const t = $('vrUpdateTitle'), sub = $('vrUpdateSub');
            if (t && opciones.titulo) t.textContent = opciones.titulo;
            if (sub && opciones.subtitulo) sub.textContent = opciones.subtitulo;
        } else {
            rotarMensajes(opciones.mensajes);
        }
    }

    function ocultar() {
        const overlay = $('vrUpdateOverlay');
        if (!overlay) return;
        clearInterval(temporizadorMsg);
        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function conectar() {
        const overlay = $('vrUpdateOverlay');
        if (!overlay) return;


        overlay.addEventListener('click', () => {
            if (overlay.classList.contains('is-success')) ocultar();
        });

        document.querySelectorAll('form[data-overlay-actualizacion]').forEach((form) => {
            form.addEventListener('submit', function (e) {
                if (e.defaultPrevented) return;
                if (form.dataset.enviando) return;

                e.preventDefault();
                form.dataset.enviando = '1';

                try { sessionStorage.setItem(CONFIG.claveSesion, '1'); } catch (_) {}

                mostrar('loading');
                setTimeout(() => form.submit(), CONFIG.retrasoEnvio);
                setTimeout(() => {
                    if (overlay.classList.contains('is-loading')) ocultar();
                }, CONFIG.tiempoMaximo);
            });
        });
        let veniaGuardando = false;
        try {
            veniaGuardando = sessionStorage.getItem(CONFIG.claveSesion) === '1';
            sessionStorage.removeItem(CONFIG.claveSesion);
        } catch (_) {}

        const hayExito = !!document.querySelector(CONFIG.selectorExito);

        if (hayExito && veniaGuardando) {
            mostrar('success');
            setTimeout(ocultar, CONFIG.duracionExito);
        }
    }

    global.OverlayActualizacion = {
        cargando: (o) => mostrar('loading', o),
        exito:    (o) => mostrar('success', o),
        ocultar:  ocultar,
        config:   CONFIG
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', conectar);
    } else {
        conectar();
    }

})(window);

/* ==========================================================
   SISTEMA DE AVISOS
   Usa el modal #modalAviso (.aviso-overlay) para todos los
   mensajes y confirmaciones de la vista. Sustituye a las
   alertas flotantes y a los alert()/confirm() del navegador.

   mostrarAviso("Guardado", "ok");          // ok | error | warn | info
   confirmarAviso("¿Continuar?", () => {}); // con callback
   if (await confirmarAviso("¿Continuar?")) { ... }   // con promesa
========================================================== */
(function (global) {
  'use strict';

  const svg = (contenido) =>
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"' +
    ' stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' + contenido + '</svg>';

  const TIPOS = {
    ok:      { titulo: 'Listo',                 boton: 'Aceptar',   icono: svg('<circle cx="12" cy="12" r="10"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>') },
    error:   { titulo: 'Ocurrió un problema',   boton: 'Entendido', icono: svg('<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>') },
    warn:    { titulo: 'Atención',              boton: 'Entendido', icono: svg('<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>') },
    info:    { titulo: 'Información',           boton: 'Aceptar',   icono: svg('<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>') },
    confirm: { titulo: 'Confirmar',             boton: 'Aceptar',   icono: svg('<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>') }
  };

  const $ = (id) => document.getElementById(id);
  let resolver = null;

  function cerrar(valor) {
    const overlay = $('modalAviso');
    if (overlay) {
      overlay.classList.remove('show');
      overlay.setAttribute('aria-hidden', 'true');
    }
    const fn = resolver;
    resolver = null;
    if (fn) fn(valor);
  }

  function abrir(tipo, mensaje, opciones) {
    opciones = opciones || {};
    const cfg = TIPOS[tipo] || TIPOS.info;
    const overlay = $('modalAviso');

    // Sin el HTML del modal se cae con elegancia al comportamiento nativo
    if (!overlay) {
      if (tipo === 'confirm') return Promise.resolve(global.confirm(mensaje));
      global.console.warn('[Aviso]', mensaje);
      return Promise.resolve(true);
    }

    if (resolver) { const fn = resolver; resolver = null; fn(false); }

    overlay.className = 'aviso-overlay tipo-' + tipo;

    const icono = $('avisoIcono');
    if (icono) {
      icono.className = 'aviso-icono ' + tipo;
      icono.innerHTML = cfg.icono;
    }

    const titulo = $('avisoTitulo');
    if (titulo) titulo.textContent = opciones.titulo || cfg.titulo;

    const texto = $('avisoTexto');
    if (texto) {
      if (opciones.html) texto.innerHTML = mensaje;
      else texto.textContent = mensaje == null ? '' : String(mensaje);
    }

    const btnOk = $('avisoAceptar');
    const btnCancel = $('avisoCancelar');

    if (btnOk) {
      btnOk.textContent = opciones.textoOk || cfg.boton;
      btnOk.style.background = '';          // el color lo pone el CSS según el tipo
      btnOk.onclick = () => cerrar(true);
    }

    if (btnCancel) {
      btnCancel.textContent = opciones.textoCancelar || 'Cancelar';
      btnCancel.style.display = (tipo === 'confirm') ? 'inline-block' : 'none';
      btnCancel.onclick = () => cerrar(false);
    }

    overlay.onclick = (e) => { if (e.target === overlay) cerrar(false); };
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => btnOk && btnOk.focus(), 60);

    return new Promise((res) => { resolver = res; });
  }

  document.addEventListener('keydown', (e) => {
    const overlay = $('modalAviso');
    if (!overlay || !overlay.classList.contains('show')) return;
    if (e.key === 'Escape') cerrar(false);
    if (e.key === 'Enter') { e.preventDefault(); cerrar(true); }
  });

  // ---- API pública ----
  function mostrarAviso(mensaje, tipo, opciones) {
    return abrir(tipo || 'ok', mensaje, opciones);
  }

  // Mantiene la firma original: confirmarAviso(mensaje, onOk, titulo)
  function confirmarAviso(mensaje, onOk, titulo) {
    const p = abrir('confirm', mensaje, {
      titulo: titulo || 'Confirmar',
      textoOk: (arguments[3] && arguments[3].textoOk) || 'Aceptar'
    });
    if (typeof onOk === 'function') p.then((ok) => { if (ok) onOk(); });
    return p;
  }

  global.mostrarAviso = mostrarAviso;
  global.confirmarAviso = confirmarAviso;

  global.Aviso = {
    exito:       (m, o) => abrir('ok', m, o),
    error:       (m, o) => abrir('error', m, o),
    advertencia: (m, o) => abrir('warn', m, o),
    info:        (m, o) => abrir('info', m, o),
    confirmar:   (o) => {
      o = (typeof o === 'string') ? { mensaje: o } : (o || {});
      return abrir('confirm', o.mensaje, o);
    },
    cerrar: () => cerrar(false)
  };

  /* --- Compatibilidad: si algo sigue llamando a alertify,
     se abre este modal en vez de un toast flotante. --- */
  const shim = {
    success: (m) => abrir('ok', m),
    error:   (m) => abrir('error', m),
    warning: (m) => abrir('warn', m),
    message: (m) => abrir('info', m),
    notify:  (m) => abrir('info', m),
    set:     () => shim,
    confirm: function (titulo, mensaje, alAceptar, alCancelar) {
      const textos = { ok: 'Aceptar', cancel: 'Cancelar' };
      const api = {
        set: function (clave, valor) {
          if (clave === 'labels' && valor) {
            if (valor.ok) textos.ok = valor.ok;
            if (valor.cancel) textos.cancel = valor.cancel;
          }
          return api;
        }
      };
      setTimeout(() => {
        abrir('confirm', mensaje, {
          titulo: titulo, html: true,
          textoOk: textos.ok, textoCancelar: textos.cancel
        }).then((ok) => { ok ? (alAceptar && alAceptar()) : (alCancelar && alCancelar()); });
      }, 0);
      return api;
    }
  };
  global.alertify = shim;

})(window);

/* ==============================
   UTILIDADES
============================== */
const $ = (s) => document.querySelector(s);
const $$ = (s) => Array.from(document.querySelectorAll(s));
const Fmx = (v) =>
  "$" +
  Number(v || 0).toLocaleString("es-MX", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }) +
  " MXN";

const toDMY = (dateStr) => {
  if (!dateStr) return "";
  const s = String(dateStr).trim();
  const iso = s.includes("T") ? s.split("T")[0] : s;
  const parts = iso.split("-");
  if (parts.length !== 3) return s;
  const [y, m, d] = parts;
  if (!y || !m || !d) return s;
  return `${d.padStart(2, "0")}/${m.padStart(2, "0")}/${y}`;
};

/* ==============================
   DOM READY
============================== */
window.addEventListener("DOMContentLoaded", () => {
  console.log("✅ JS cargado correctamente - Reservaciones Activas");

  /* ==============================
     FILTRO FECHA
  ============================== */
  function initFiltroFechaFlatpickr() {
    if (!window.flatpickr) return;

    const inputUI = document.getElementById("filtro_fecha_ui");
    const inputHidden = document.getElementById("filtro_fecha");
    const form = document.querySelector("form.toolbar");

    if (!inputUI || !inputHidden || !form) return;

    let backdrop = document.querySelector(".fp-backdrop");

    if (!backdrop) {
      backdrop = document.createElement("div");
      backdrop.className = "fp-backdrop";
      document.body.appendChild(backdrop);
    }

    function openModal(instance) {
      backdrop.classList.add("is-open");
      document.body.classList.add("no-scroll");
      backdrop.onclick = () => instance.close();
    }

    function closeModal() {
      backdrop.classList.remove("is-open");
      document.body.classList.remove("no-scroll");
      backdrop.onclick = null;
    }

    function makeActions(instance) {
      const actions = document.createElement("div");
      actions.className = "fp-actions";

      actions.innerHTML = `
        <button type="button" class="fp-today">Hoy</button>
        <button type="button" class="fp-clear">Limpiar</button>
        <button type="button" class="fp-label">✖ Fecha</button>
      `;

      actions.querySelector(".fp-today").addEventListener("click", () => {
        instance.setDate(new Date(), true);
      });

      actions.querySelector(".fp-clear").addEventListener("click", () => {
        instance.clear();
        inputHidden.value = "";
        form.submit();
      });

      return actions;
    }

    flatpickr(inputUI, {
      locale: "es",
      dateFormat: "d-M-Y",
      allowInput: false,
      clickOpens: true,
      minDate: "today",

      onOpen: (selectedDates, dateStr, instance) => {
        openModal(instance);

        if (!instance._actionsAdded) {
          instance.calendarContainer.appendChild(makeActions(instance));
          instance._actionsAdded = true;
        }
      },

      onClose: () => closeModal(),

      onChange: (selectedDates) => {
        const d = selectedDates?.[0];

        if (d) {
          const year = d.getFullYear();
          const month = String(d.getMonth() + 1).padStart(2, "0");
          const day = String(d.getDate()).padStart(2, "0");

          inputHidden.value = `${year}-${month}-${day}`;
        } else {
          inputHidden.value = "";
        }

        form.submit();
      }
    });
  }

  initFiltroFechaFlatpickr();

  /* ==============================
     TOGGLE RESUMEN
  ============================== */
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-plus");
    if (!btn) return;

    const row = btn.closest(".row");
    const detail = row.nextElementSibling;

    if (!detail || !detail.classList.contains("row-detail")) return;

    const isVisible = detail.style.display === "block";

    detail.style.display = isVisible ? "none" : "block";

    btn.textContent = isVisible ? "+" : "-";
  });

  /* ==============================
     EDITAR
  ============================== */
  document.addEventListener("click", function (e) {
    console.log("CLICK DETECTADO", e.target);

    const btn = e.target.closest(".btn-edit-direct");

    if (!btn) {
      console.log("NO es botón editar");
      return;
    }

    console.log("✅ BOTÓN EDITAR DETECTADO");

    const row = btn.closest(".row-detail")?.previousElementSibling;

    console.log("ROW:", row);

    current = {
      codigo: row.dataset.codigo,
      nombre_cliente: row.dataset.cliente,
      email_cliente: row.dataset.email,
      telefono_cliente: row.dataset.numero,
      fecha_inicio: row.dataset.fechaSalida,
      hora_retiro: row.dataset.hora_retiro,
      fecha_fin: row.dataset.fechaFin,
      hora_entrega: row.dataset.hora_entrega
    };

    console.log("🧾 CURRENT:", current);

    openEditModal();
  });

  /* ==============================
     ELIMINAR
  ============================== */
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-delete-direct");
    if (!btn) return;

    const url = btn.dataset.url;

    const form = document.getElementById("aDeleteForm");
    form.action = url;

    form.submit();
  });

 /* ==========================================================
      Reenviar correo.
    =========================================================== */
  window.reenviarCorreo = function (id, btn) {


    confirmarAviso("¿Reenviar el correo de confirmación al cliente?", () => {
      btn.disabled = true;
      const originalText = btn.innerHTML;
      btn.innerHTML = "Enviando... ⏳";

      fetch(`/reservaciones/${id}/reenviar-correo`, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        }
      })
        .then(res => res.json())
        .then(data => {
          mostrarAviso(data.message || "Correo reenviado correctamente.", "ok");
        })
        .catch(() => {
          mostrarAviso("Error al enviar el correo. Intenta de nuevo.", "error");
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = originalText;
        });
    });
  };
  // confirmarAviso() y mostrarAviso() viven en el bloque de avisos
  // que está al inicio del archivo, para que estén disponibles en
  // toda la vista (incluidos los módulos externos).
  /* ==============================
     FORMATEAR FECHA SEGURO
  ============================== */
  function formatearFechaSeguro(fecha) {
    if (!fecha) return '-';

    const meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    if (typeof fecha === 'string' && fecha.includes('-')) {
        const partes = fecha.split('-');
        if (partes.length === 3) {
            const anio = partes[0];
            const mes = parseInt(partes[1]) - 1;
            const dia = partes[2].padStart(2, '0');
            return `${dia}-${meses[mes]}-${anio}`;
        }
    }

    const date = new Date(fecha);
    if (!isNaN(date.getTime())) {
        const dia = String(date.getDate()).padStart(2, '0');
        const mes = meses[date.getMonth()];
        const anio = date.getFullYear();
        return `${dia}-${mes}-${anio}`;
    }

    return String(fecha);
  }

  /* ==============================
     MODAL VEHICULOS
  ============================== */
  const modalVehiculos = document.getElementById("modalVehiculos");
  const tablaVehiculos = document.getElementById("tablaVehiculos");

  document.addEventListener("click", async (e) => {

    const btn = e.target.closest(".btn-apartar-auto");
    if (!btn) return;

    modalVehiculos.classList.add("show");

    tablaVehiculos.innerHTML = `<tr><td colspan="13">Cargando...</td></tr>`;

    try {
        const res = await fetch('/admin/vehiculos-disponibles');
        const data = await res.json();

        tablaVehiculos.innerHTML = "";

        data.forEach(v => {
            const fraccion = v.gasolina_fraccion ?? 0;
            const litros = v.gasolina_actual ?? 0;
            const km = v.kilometraje ?? 0;

            const fechaSeguro = v.fin_vigencia_poliza
                ? formatearFechaSeguro(v.fin_vigencia_poliza)
                : '-';

            tablaVehiculos.innerHTML += `
                <tr data-id-vehiculo="${v.id_vehiculo}"
                    data-placa="${v.placa ?? '-'}"
                    data-color="${v.color ?? '-'}"
                    data-categoria="${v.tamano ?? v.categoria ?? '-'}"
                    data-gas-original="${fraccion}"
                    data-km-original="${km}">
                    <td>${v.placa ?? '-'}</td>
                    <td><span class="chip-cat">${v.categoria ?? '-'}</span></td>
                    <td>${v.tamano ?? '-'}</td>
                    <td>${v.modelo ?? '-'}</td>
                    <td>${v.transmision ?? '-'}</td>
                    <td>${v.color ?? '-'}</td>
                    <td class="celda-editable" data-tipo="gas">
                        <span class="celda-valor">${fraccion}/16</span>
                        <button type="button" class="btn-edit-inline" aria-label="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></button>
                    </td>
                    <td class="celda-litros">${litros}</td>
                    <td class="celda-editable" data-tipo="km">
                        <span class="celda-valor">${Number(km).toLocaleString()}</span>
                        <button type="button" class="btn-edit-inline" aria-label="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg></button>
                    </td>
                    <td>${v.vigencia_verificacion ?? '-'}</td>
                    <td>${v.intervalo_km ?? '-'}</td>
                    <td>${fechaSeguro}</td>
                    <td>
                        <button class="btn btn-select-auto" data-id="${v.id_vehiculo}">Seleccionar</button>
                    </td>
                </tr>
            `;
        });

    } catch (err) {
        console.error(err);
        tablaVehiculos.innerHTML = `<tr><td colspan="13">Error al cargar vehículos</td></tr>`;
    }
  });

  document.getElementById("vClose")?.addEventListener("click", () => {
    modalVehiculos.classList.remove("show");
  });

  document.getElementById("vCancel")?.addEventListener("click", () => {
    modalVehiculos.classList.remove("show");
  });

  let reservacionSeleccionada = null;

  document.addEventListener("click", async (e) => {

    const btn = e.target.closest(".btn-apartar-auto");
    if (!btn) return;

    reservacionSeleccionada = btn.dataset.id;

    modalVehiculos.classList.add("show");
  });

  document.addEventListener("click", async (e) => {

    const btn = e.target.closest(".btn-select-auto");
    if (!btn) return;

    const idVehiculo = btn.dataset.id;

    if (!reservacionSeleccionada) {
      mostrarAviso("No hay reservación seleccionada", "warn");
      return;
    }

    try {
      OverlayActualizacion.cargando();

      const res = await fetch('/admin/crear-contrato', {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          id_reservacion: reservacionSeleccionada,
          id_vehiculo: idVehiculo
        })
      });

      const data = await res.json();

      if (!data.success) throw new Error(data.message);

      OverlayActualizacion.exito({
        titulo: "¡Vehículo asignado!",
        subtitulo: "Abriendo el checklist de salida…"
      });

      setTimeout(() => {
        window.location.href = `/admin/reservacion/${data.id_contrato}/checklist?modo=salida&from=apartar`;
      }, 1200);

    } catch (err) {
      console.error(err);
      OverlayActualizacion.ocultar();
      mostrarAviso("Error al crear contrato", "error");
    }
  });

  /* ==============================
     EDICION INLINE INVENTARIO
  ============================== */
  (function initEdicionInventarioRA() {
    const tbody = document.getElementById("tablaVehiculos");
    const modalConf = document.getElementById("modalConfirmInv");
    if (!tbody || !modalConf) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let pendiente = null;

    const activar = (celda) => {
      if (celda.querySelector("input")) return;
      const fila = celda.closest("tr");
      const span = celda.querySelector(".celda-valor");
      const tipo = celda.dataset.tipo;
      const original = tipo === "gas"
        ? (fila.dataset.gasOriginal || "0")
        : (fila.dataset.kmOriginal || "0");

      const input = document.createElement("input");
      input.type = "number"; input.value = original; input.min = "0";
      if (tipo === "gas") input.max = "16";
      Object.assign(input.style, { width: "70px", border: "1px solid #D6121F", borderRadius: "6px", padding: "3px 6px", fontWeight: "bold", textAlign: "center" });

      span.style.display = "none";
      celda.querySelector(".btn-edit-inline").style.display = "none";
      celda.appendChild(input);
      input.focus(); input.select();

      const cancelar = () => {
        input.remove();
        span.style.display = "";
        celda.querySelector(".btn-edit-inline").style.display = "";
      };

      const confirmar = () => {
        const nuevo = parseFloat(input.value);
        const orig = parseFloat(original);
        if (isNaN(nuevo) || nuevo === orig) { cancelar(); return; }
        if (tipo === "gas" && (nuevo < 0 || nuevo > 16)) { mostrarAviso("La gasolina debe estar entre 0 y 16.", "warn"); cancelar(); return; }
        if (nuevo < 0) { cancelar(); return; }

        pendiente = {
          fila, celda, tipo, span,
          idVehiculo: fila.dataset.idVehiculo,
          valorNuevo: nuevo,
          fraccionNueva: nuevo,
          restaurar: cancelar,
        };

        document.getElementById("ciCategoria").textContent = fila.dataset.categoria;
        document.getElementById("ciColor").textContent = fila.dataset.color;
        document.getElementById("ciPlacas").textContent = fila.dataset.placa;
        document.getElementById("ciCampoLabel").textContent = tipo === "gas" ? "Gasolina" : "Kilometraje";
        document.getElementById("ciAnterior").textContent = tipo === "gas" ? `${orig}/16` : orig.toLocaleString();
        document.getElementById("ciNuevo").textContent = tipo === "gas" ? `${nuevo}/16` : nuevo.toLocaleString();

        modalConf.classList.add("show");
      };

      input.addEventListener("keydown", (e) => { if (e.key === "Enter") confirmar(); if (e.key === "Escape") cancelar(); });
      input.addEventListener("blur", confirmar);
    };

    tbody.addEventListener("click", (e) => {
      const btn = e.target.closest(".btn-edit-inline");
      if (!btn) return;
      e.stopPropagation();
      activar(btn.closest(".celda-editable"));
    });

    const cerrar = (restaurar = true) => {
      modalConf.classList.remove("show");
      if (restaurar && pendiente) pendiente.restaurar();
      pendiente = null;
    };
    document.getElementById("ciCancel")?.addEventListener("click", () => cerrar(true));
    document.getElementById("ciClose")?.addEventListener("click", () => cerrar(true));
    modalConf.addEventListener("click", (e) => { if (e.target === modalConf) cerrar(true); });

    document.getElementById("ciConfirm")?.addEventListener("click", async () => {
      if (!pendiente) return;
      const p = pendiente;
      const btn = document.getElementById("ciConfirm");
      btn.disabled = true; const txt = btn.innerHTML; btn.innerHTML = "Guardando...";
      try {
        const res = await fetch("/admin/vehiculo/actualizar-inventario", {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrf },
          body: JSON.stringify({
            id_vehiculo: p.idVehiculo,
            campo: p.tipo === "gas" ? "gasolina" : "kilometraje",
            valor: p.tipo === "gas" ? p.fraccionNueva : p.valorNuevo,
          }),
        });
        const data = await res.json();
        if (res.ok && (data.success || data.ok)) {
          if (p.tipo === "gas") {
            p.span.textContent = `${p.fraccionNueva}/16`;
            p.fila.dataset.gasOriginal = p.fraccionNueva;
            const cl = p.fila.querySelector(".celda-litros");
            if (cl) cl.textContent = data.litros ?? cl.textContent;
          } else {
            p.span.textContent = p.valorNuevo.toLocaleString();
            p.fila.dataset.kmOriginal = p.valorNuevo;
          }
          p.celda.querySelector("input")?.remove();
          p.span.style.display = "";
          p.celda.querySelector(".btn-edit-inline").style.display = "";
          window.alertify?.success?.("Inventario actualizado.");
          modalConf.classList.remove("show");
          pendiente = null;
        } else {
          throw new Error(data.error || "Error backend");
        }
      } catch (err) {
        console.error(err);
        mostrarAviso("No se pudo guardar: " + err.message, "error");
        cerrar(true);
      } finally {
        btn.disabled = false; btn.innerHTML = txt;
      }
    });
  })();

  /* ==============================
     MODAL RESERVACIONES ANTERIORES
  ============================== */
  const modalPrev = $("#modalPrev");
  const btnPrev = $("#btnPrevBookings");
  const pClose = $("#pClose");
  const pCancel = $("#pCancel");

  function openPrevModal() {
    if (!modalPrev) return;
    modalPrev.classList.add("show");
    modalPrev.setAttribute("aria-hidden", "false");
  }
  function closePrevModal() {
    if (!modalPrev) return;
    modalPrev.classList.remove("show");
    modalPrev.setAttribute("aria-hidden", "true");
  }

  btnPrev?.addEventListener("click", openPrevModal);
  pClose?.addEventListener("click", closePrevModal);
  pCancel?.addEventListener("click", closePrevModal);

  modalPrev?.addEventListener("click", (e) => {
    if (e.target === modalPrev) closePrevModal();
  });

  /* ==============================
     BUSQUEDA AJAX
  ============================== */
  (function initBusquedaAjax() {
    const inputQ = $("#q");
    const tbody = $("#tablaActivas .tbody");
    const count = $("#count");
    if (!inputQ || !tbody) return;

    const esAeropuerto = new URLSearchParams(location.search).get("sucursal") === "1";

    let debounceTimer = null;
    let ultimoController = null;
    const filasOriginales = tbody.innerHTML;

    const escapeHtml = (s) =>
      String(s ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    const fmtFecha = (f) => {
      if (!f) return "—";
      const d = new Date(String(f).includes("T") ? f : f + "T00:00:00");
      if (isNaN(d)) return f;
      const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
      return `${String(d.getDate()).padStart(2, "0")}-${meses[d.getMonth()]}-${d.getFullYear()}`;
    };

    const fmtFechaHora = (f) => {
      if (!f) return "—";
      const d = new Date(String(f).replace(" ", "T"));
      if (isNaN(d)) return f;
      const meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
      const fecha = `${String(d.getDate()).padStart(2, "0")}-${meses[d.getMonth()]}-${d.getFullYear()}`;
      const hora = `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}:${String(d.getSeconds()).padStart(2, "0")}`;
      return `${fecha} ${hora}`;
    };

    const fmtHora = (h) => (h ? String(h).slice(0, 5) : "—");

    const colorEstado = (estado) => {
      switch (estado) {
        case "confirmada": return "ok";
        case "pendiente_pago": return "warn";
        case "hold": return "gray";
        case "cancelada": return "danger";
        default: return "gray";
      }
    };

    const oficinaHtml = (of) => {
      if (of === "AIQ") return `<span class="oficina-icon"><i class="fa-solid fa-plane"></i> AIQ</span>`;
      if (of === "TAQ") return `<span class="oficina-icon"><i class="fa-solid fa-bus" style="color:black;"></i> TAQ</span>`;
      if (of === "OCP") return `<span class="oficina-icon"><i class="fa-solid fa-building"></i> OCP</span>`;
      return "—";
    };

    const primeraOficina = (...oficinas) => {
      const oficina = oficinas.find(valor => {
        const texto = String(valor ?? "").trim();
        return texto !== "" && !/^\d+$/.test(texto);
      });
      return oficina ? String(oficina).trim() : "—";
    };

    const nombreCompletoOficina = (oficina) => {
      const nombres = {
        AIQ: "Querétaro Aeropuerto",
        TAQ: "Querétaro Central de Autobuses",
        OCP: "Querétaro Oficina Plaza Central Park",
      };
      return nombres[oficina] || oficina;
    };

    function construirFila(r) {
      const nombre = r.nombre_completo && r.nombre_completo !== ""
        ? r.nombre_completo
        : (r.nombre_cliente || "—");

      const vueloCol = esAeropuerto ? `<div>${escapeHtml(r.no_vuelo || "—")}</div>` : "";

      const totalFmt = "$" + Number(r.total || 0).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      }) + " MXN";

      const costoOnline = "$" + Number(r.precio_dia || 0).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      });
      const costoOficina = "$" + Number((r.precio_dia || 0) * 1.15).toLocaleString("es-MX", {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
      });

      let extrasHtml = `<span style="color:#999;">Ninguno</span>`;
      if (r.extras && r.extras.length) {
        extrasHtml = r.extras.map(e => `<div>- ${escapeHtml(e.nombre)} (x${escapeHtml(e.cantidad)})</div>`).join("");
      }

      const oficinaRecoleccion = nombreCompletoOficina(primeraOficina(
        r.oficina_retiro_completa,
        r.oficina_retiro,
        r.oficina_compacta
      ));
      const oficinaDevolucionPropia = nombreCompletoOficina(primeraOficina(
        r.oficina_devolucion_completa,
        r.oficina_devolucion
      ));
      const oficinaDevolucion = oficinaDevolucionPropia === "—"
        ? oficinaRecoleccion
        : oficinaDevolucionPropia;

      const estadoTxt = (r.estado || "").charAt(0).toUpperCase() + (r.estado || "").slice(1);

      return `
        <div class="row"
          data-codigo="${escapeHtml(r.codigo)}"
          data-cliente="${escapeHtml(nombre)}"
          data-email="${escapeHtml(r.email_cliente || "")}"
          data-numero="${escapeHtml(r.telefono_cliente || "")}"
          data-categoria="${escapeHtml(r.categoria || "")}"
          data-fecha-salida="${escapeHtml(r.fecha_inicio_ymd || "")}"
          data-estado="${escapeHtml(r.estado || "")}"
          data-sucursal="${escapeHtml(r.sucursal_retiro || "")}"
          data-hora_retiro="${escapeHtml(r.hora_retiro || "")}"
          data-fecha_fin="${escapeHtml(r.fecha_fin_ymd || "")}"
          data-hora_entrega="${escapeHtml(r.hora_entrega || "")}"
        >
          <div><button type="button" class="btn-more" data-toggle-detail>+</button></div>
          <div>${escapeHtml(r.codigo)}</div>
          <div>${oficinaHtml(r.oficina_compacta)}</div>
          <div>${fmtFecha(r.fecha_inicio)}</div>
          <div>${fmtHora(r.hora_retiro)}</div>
          ${vueloCol}
          <div>${escapeHtml(r.categoria || "")}</div>
          <div>${escapeHtml(r.dias)}</div>
          <div>${escapeHtml(nombre)}</div>
          <div>${escapeHtml(r.telefono_cliente || "—")}</div>
          <div>${escapeHtml(r.email_cliente || "—")}</div>
          <div><span class="state ${colorEstado(r.estado)}">${escapeHtml(estadoTxt)}</span></div>
          <div>${totalFmt}</div>
        </div>

        <div class="row-detail" style="display:none;">
          <div class="reserva-summary">
            <div class="summary-title">Reservación Confirmada el: ${fmtFechaHora(r.created_at)}</div>
            <div class="reserva-summary-line summary-full summary-contact"><b>Datos de Contacto:</b> ${escapeHtml(r.pais_cliente || r.pais || "MEXICO (MX)")} | ${escapeHtml(r.telefono_cliente || "—")} | ${escapeHtml(nombre)} | ${escapeHtml(r.email_cliente || "—")}</div>
            <div class="reserva-summary-line"><b>Entrega:</b> ${fmtFecha(r.fecha_inicio)} a las ${fmtHora(r.hora_retiro)} HRS</div>
            <div class="reserva-summary-line"><b>Devolución:</b> ${fmtFecha(r.fecha_fin)} a las ${fmtHora(r.hora_entrega)} HRS</div>
            <div class="reserva-summary-line"><b>Oficina de entrega:</b> ${escapeHtml(oficinaRecoleccion)}</div>
            <div class="reserva-summary-line"><b>Oficina de devolución:</b> ${escapeHtml(oficinaDevolucion)}</div>
            <div class="reserva-summary-line summary-full">
              <b>Vehículo Requerido:</b> ${escapeHtml(r.categoria || "")} | ${escapeHtml(r.categoria_nombre || "Sin asignar")} ${escapeHtml(r.transmision || "Sin transmisión")} ${escapeHtml(r.categoria_descripcion || "")} | Costo online: ${costoOnline} | Costo oficina: ${costoOficina}
            </div>
            <div class="reserva-summary-line"><b>Número de vuelo:</b> ${escapeHtml(r.no_vuelo || "—")}</div>
            <div class="reserva-summary-line"><b>Adicionales Requeridos:</b> ${extrasHtml}</div>
            <div class="reserva-summary-line"><b>Seguros:</b><br>${r.seguro ? escapeHtml(r.seguro) : "—"}</div>
            <div class="reserva-summary-line summary-total summary-total-devolucion"><b>Total(MXN):</b> ${totalFmt} - Forma de pago: (${escapeHtml(r.metodo_pago || "mostrador")})</div>

            <div class="summary-actions">
              <div class="summary-actions-left">
                <button type="button" class="btn btn-edit" onclick="window.location.href='/admin/reservaciones/${r.id_reservacion}/editar'">
                  <i class="fa-solid fa-pen"></i> Editar Reservación
                </button>
                <button type="button" class="btn btn-cancel" title="Cancelar reservación" data-open-actions data-id="${r.id_reservacion}" data-codigo="${escapeHtml(r.codigo)}" data-delete-url="${r.delete_url}">
                  <i class="fa-solid fa-trash"></i> Cancelar Reservación
                </button>
              </div>
              <div class="summary-actions-right">
                <button type="button" class="btn btn-mail" onclick="reenviarCorreo(${r.id_reservacion}, this)">
                  <i class="fa-solid fa-envelope"></i> Reenviar correo
                </button>
                <button type="button" class="btn btn-car btn-apartar-auto" data-id="${r.id_reservacion}">
                  <i class="fa-solid fa-car-side"></i> Apartar auto
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    async function buscar(q) {
      if (ultimoController) ultimoController.abort();
      ultimoController = new AbortController();

      const params = new URLSearchParams(location.search);
      params.set("q", q);

      try {
        const res = await fetch(`${location.pathname}?${params.toString()}`, {
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json",
          },
          signal: ultimoController.signal,
        });

        const data = await res.json();

        if (!data.success) throw new Error("Respuesta inválida");

        if (!data.data.length) {
          tbody.innerHTML = `<div class="row"><div style="grid-column: 1 / -1; text-align:center;">No se encontraron reservaciones.</div></div>`;
        } else {
          tbody.innerHTML = data.data.map(construirFila).join("");
        }

        if (count) count.textContent = data.total;
      } catch (err) {
        if (err.name === "AbortError") return;
        console.error("Error en búsqueda:", err);
      }
    }

    inputQ.addEventListener("input", () => {
      const q = inputQ.value.trim();

      clearTimeout(debounceTimer);

      if (q === "") {
        if (ultimoController) ultimoController.abort();
        tbody.innerHTML = filasOriginales;
        if (count) count.textContent = $$("#tablaActivas .tbody .row").length;
        return;
      }

      debounceTimer = setTimeout(() => buscar(q), 300);
    });
  })();

  /* ==============================
     MODAL DETALLE
  ============================== */
  let current = null;

  async function openModal(row) {
    const codigo = row.dataset.codigo?.trim();
    if (!codigo) {
      console.warn("⚠️ No se encontró código en la fila seleccionada");
      return;
    }

    console.log(`📦 Consultando reservación ${codigo}...`);

    try {
      const resp = await fetch(`/admin/reservaciones-activas/${encodeURIComponent(codigo)}`);
      if (!resp.ok) throw new Error(`Error ${resp.status}`);

      const data = await resp.json();
      console.log("🧾 Datos recibidos:", data);

      current = data;

      $("#mTitle").textContent = `Detalle Reservación ${data.codigo || "—"}`;

      const fullName = [data.nombre_cliente, data.apellidos_cliente]
        .filter(Boolean)
        .join(" ")
        .trim();

      const fmtFecha = (fechaStr, horaStr) => {
        if (!fechaStr) return "—";
        const meses = ["ene","feb","mar","abr","may","jun","jul","ago","sep","oct","nov","dic"];
        const [y, m, d] = String(fechaStr).split("-");
        const mesTxt = meses[parseInt(m, 10) - 1] || m;
        const hora = horaStr ? ` ${String(horaStr).slice(0, 5)}` : "";
        return `${String(d).padStart(2,"0")}-${mesTxt}-${y}${hora}`;
      };

      const salida  = fmtFecha(data.fecha_inicio, data.hora_retiro);
      const entrega = fmtFecha(data.fecha_fin, data.hora_entrega);

      $("#mFechas").textContent = `${salida} al ${entrega}`;
      $("#mVehiculo").textContent = `${data.categoria || ""} ${data.categoria_nombre || ""} ${data.categoria_descripcion || ""}`;
      $("#mCliente").textContent = data.nombre_completo || data.nombre_cliente || "—";

      const comentarioRaw = data.comentarios ?? "";
      const tieneComentarios = String(comentarioRaw).trim() !== "";

      if (!tieneComentarios) {
        const url = `/admin/contrato/${encodeURIComponent(data.id_reservacion)}`;
        console.log("➡️ Sin comentarios: redirigiendo directo a Contrato:", url);
        window.location.href = url;
        return;
      }

      $("#mComentarios").textContent = String(comentarioRaw).trim();

      $("#modal").classList.add("show");
      console.log("🪟 Modal abierto (hay comentarios):", current);
    } catch (err) {
      console.error("❌ Error al obtener detalles de la reservación:", err);
      mostrarAviso("Error al obtener la información de la reservación. Intente nuevamente.", "error");
    }
  }

  function closeModal() {
    $("#modal").classList.remove("show");
    console.log("❎ Modal cerrado");
  }

  $("#mClose")?.addEventListener("click", closeModal);
  $("#mCancel")?.addEventListener("click", closeModal);

  /* ==============================
     CLICK EN FILA
  ============================== */
  document.addEventListener("click", (ev) => {
    const row = ev.target.closest(".table .tbody .row");
    if (!row) return;

    if (ev.target.closest("button, a, form, input, select, textarea")) return;

    openModal(row);
  });

  /* ==============================
     CAPTURAR CONTRATO
  ============================== */
  $("#mGo")?.addEventListener("click", () => {
    if (!current) return;
    const url = `/admin/contrato/${encodeURIComponent(current.id_reservacion)}`;
    console.log("➡️ Redirigiendo a vista Contrato:", url);
    window.location.href = url;
  });

  /* ==============================
     MODAL EDICION
  ============================== */
  function openEditModal() {
    if (!current) return;

    $("#eTitle").textContent = `Editar ${current.codigo || ""}`;

    $("#eNombre").value = current.nombre_cliente || "";
    $("#eCorreo").value = current.email_cliente || "";
    $("#eTelefono").value = current.telefono_cliente || "";

    $("#eFechaInicio").value = current.fecha_inicio || "";
    $("#eHoraRetiro").value = (current.hora_retiro || "").slice(0, 5);

    $("#eFechaFin").value = current.fecha_fin || "";
    $("#eHoraEntrega").value = (current.hora_entrega || "").slice(0, 5);

    $("#modalEdit").classList.add("show");
  }

  function closeEditModal() {
    $("#modalEdit").classList.remove("show");
  }

  $("#mEdit")?.addEventListener("click", openEditModal);
  $("#eClose")?.addEventListener("click", closeEditModal);
  $("#eCancel")?.addEventListener("click", closeEditModal);

  /* ==============================
     GUARDAR CAMBIOS
  ============================== */
  $("#eSave")?.addEventListener("click", async () => {
    if (!current) return;

    const payload = {
      nombre_cliente: $("#eNombre").value.trim(),
      email_cliente: $("#eCorreo").value.trim(),
      telefono_cliente: $("#eTelefono").value.trim(),
      fecha_inicio: $("#eFechaInicio").value,
      hora_retiro: $("#eHoraRetiro").value,
      fecha_fin: $("#eFechaFin").value,
      hora_entrega: $("#eHoraEntrega").value
    };

    if (!payload.nombre_cliente || !payload.email_cliente || !payload.telefono_cliente) {
      mostrarAviso("Completa nombre, correo y teléfono", "warn");
      return;
    }

    if (!payload.fecha_inicio || !payload.fecha_fin) {
      mostrarAviso("Completa fecha de salida y entrega", "warn");
      return;
    }

    if (payload.fecha_fin < payload.fecha_inicio) {
      mostrarAviso("La fecha de entrega no puede ser menor que la de salida", "warn");
      return;
    }

    try {
      const res = await fetch(`/admin/reservaciones-activas/${current.id_reservacion}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message);

      Object.assign(current, payload);

      $("#mCliente").textContent = current.nombre_cliente;
      $("#mEmail").textContent = current.email_cliente;
      $("#mNumero").textContent = current.telefono_cliente;

      const salida = `${toDMY(current.fecha_inicio)} ${String(current.hora_retiro || "").slice(0, 5)}`;
      const entrega = `${toDMY(current.fecha_fin)} ${String(current.hora_entrega || "").slice(0, 5)}`;

      $("#mSalida").textContent = salida;
      $("#mEntrega").textContent = entrega;

      mostrarAviso("Reservación actualizada correctamente", "ok");
      closeEditModal();
    } catch (err) {
      console.error(err);
      mostrarAviso(err.message || "Error al guardar la reservación", "error");
    }
  });

  /* ==============================
     MODAL ACCIONES
  ============================== */
  const modalActions = $("#modalActions");
  const aClose = $("#aClose");
  const aCancel = $("#aCancel");
  const aCodigo = $("#aCodigo");
  const aIdReservacion = $("#aIdReservacion");
  const aDeleteForm = $("#aDeleteForm");

  const aExtraFields = $("#aExtraFields");
  const aComentarios = $("#aComentarios");
  const aEliminadoPor = $("#aEliminadoPor");
  const aAccion = $("#aAccion");

  function openActionsModal({ id, codigo, deleteUrl }) {
    if (!modalActions) return;

    if (aCodigo) aCodigo.textContent = codigo || "—";
    if (aIdReservacion) aIdReservacion.value = id || "";

    if (aDeleteForm && deleteUrl) aDeleteForm.setAttribute("action", deleteUrl);

    if (aExtraFields) aExtraFields.style.display = "none";
    if (aComentarios) aComentarios.value = "";
    if (aEliminadoPor) aEliminadoPor.value = "";
    if (aAccion) aAccion.value = "";

    modalActions.classList.add("show");
    modalActions.setAttribute("aria-hidden", "false");
  }

  function closeActionsModal() {
    if (!modalActions) return;
    modalActions.classList.remove("show");
    modalActions.setAttribute("aria-hidden", "true");
  }

  document.addEventListener("click", (ev) => {
    const btn = ev.target.closest("[data-open-actions]");
    if (!btn) return;

    ev.stopPropagation();
    openActionsModal({
      id: btn.dataset.id,
      codigo: btn.dataset.codigo,
      deleteUrl: btn.dataset.deleteUrl,
    });
  });

  aClose?.addEventListener("click", closeActionsModal);
  aCancel?.addEventListener("click", closeActionsModal);

  modalActions?.addEventListener("click", (e) => {
    if (e.target === modalActions) closeActionsModal();
  });

  aDeleteForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const codigo = aCodigo?.textContent || "esta reservación";
    const ok = await confirmarAviso(
      `¿Seguro que deseas ELIMINAR ${codigo}? Esta acción no se puede deshacer.`,
      null,
      "Eliminar reservación"
    );

    if (ok) aDeleteForm.submit();
  });

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  async function postAccion(url, payload = null) {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        "Accept": "application/json",
        ...(payload ? { "Content-Type": "application/json" } : {}),
      },
      ...(payload ? { body: JSON.stringify(payload) } : {}),
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.success) {
      throw new Error(data.message || "Error al ejecutar la acción");
    }

    return data;
  }

  function showExtras(tipo) {
    if (aExtraFields) aExtraFields.style.display = "grid";
    if (aAccion) aAccion.value = tipo;
  }

  function getExtrasOrStop() {
    const comentarios = (aComentarios?.value || "").trim();
    const eliminado_por = (aEliminadoPor?.value || "").trim();

    if (!comentarios) { mostrarAviso("Agrega comentarios", "warn"); return null; }
    if (!eliminado_por) { mostrarAviso("Selecciona quién lo eliminó", "warn"); return null; }

    return { comentarios, eliminado_por };
  }

  $("#aNoShow")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (aAccion?.value !== "no-show") {
      showExtras("no-show");
      return;
    }

    const payload = getExtrasOrStop();
    if (!payload) return;

    const ok = await confirmarAviso(`¿Marcar ${codigo} como NO SHOW?`, null, "Marcar como No Show");
    if (!ok) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/no-show`, payload);
      await mostrarAviso("Marcada como No Show", "ok");
      window.location.reload();
    } catch (e) {
      console.error(e);
      mostrarAviso(e.message, "error");
    }
  });

  $("#aCancelar")?.addEventListener("click", async (ev) => {
    ev.stopPropagation();
    const id = aIdReservacion?.value;
    const codigo = aCodigo?.textContent || "—";
    if (!id) return;

    if (aAccion?.value !== "cancelar") {
      showExtras("cancelar");
      return;
    }

    const payload = getExtrasOrStop();
    if (!payload) return;

    const ok = await confirmarAviso(`¿Cancelar ${codigo}?`, null, "Cancelar reservación");
    if (!ok) return;

    try {
      await postAccion(`/admin/reservaciones-activas/${id}/cancelar`, payload);
      await mostrarAviso("Reservación cancelada", "ok");
      window.location.reload();
    } catch (e) {
      console.error(e);
      mostrarAviso(e.message, "error");
    }
  });

});

/* ==============================
   MODAL CONFIRMAR VEHICULO
============================== */
(function (global) {
    'use strict';

    const CONFIG = {
        capacidadLitros: 60,
        dieciseisavos: 16,
        gasolinaPorDefecto: 16,
        cerrarConFondo: true,
        cerrarConEsc: true,
        exigirKilometraje: true
    };

    const IDS = {
        modal: 'modalConfirmarVehiculo',
        placas: 'confPlacasVehiculo',
        modelo: 'confModeloVehiculo',
        categoria: 'confCategoriaVehiculo',
        color: 'confColorVehiculo',
        select: 'confGasolinaSelect',
        litros: 'confLitrosTexto',
        km: 'confKilometrajeInput',
        btnCerrar: 'cerrarConfirmarVehiculo',
        btnCancelar: 'cancelarConfirmarVehiculo',
        btnConfirmar: 'confirmarSeleccionVehiculo'
    };

    const $id = (id) => document.getElementById(id);

    let vehiculoActual = null;
    let callbacks = {};
    let listenersListos = false;

    function notificar(tipo, mensaje) {
        if (typeof global.mostrarAviso === 'function') {
            if (tipo === 'error') return global.mostrarAviso(mensaje, 'error');
            if (tipo === 'warning') return global.mostrarAviso(mensaje, 'warn');
            return global.mostrarAviso(mensaje, 'ok');
        }
        console.warn('[ModalConfirmarVehiculo]', mensaje);
    }

    const CAPACIDAD_LITROS = CONFIG.capacidadLitros;
    const DIECISEISAVOS = CONFIG.dieciseisavos;

    const PRINCIPALES = { 0: '0', 4: '1/4', 8: '1/2', 12: '3/4', 16: '1' };

    function etiqueta(n) {
        return PRINCIPALES[n] || `${n}/16`;
    }

    function enOctavos(n) {
        if (n === 0) return 'Vacío · 0/8';
        if (n === 16) return 'Lleno · 8/8';

        if (n % 2 === 0) {
            return `${n / 2}/8`;
        }

        const bajo = Math.floor(n / 2);
        const alto = Math.ceil(n / 2);
        return `entre ${bajo}/8 y ${alto}/8`;
    }

    function litros(n) {
        return Math.round((n / DIECISEISAVOS) * CAPACIDAD_LITROS);
    }

    function inicializarDropdownGasolina() {
        const select = $id(IDS.select);
        if (!select || select.dataset.dropdownListo === '1') return;

        const wrapper = select.closest('.gasolina-select-wrapper');
        if (!wrapper) return;

        select.dataset.dropdownListo = '1';
        select.style.display = 'none';

        const dd = document.createElement('div');
        dd.className = 'gas-dropdown';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'gas-dropdown-trigger';
        trigger.innerHTML = `<span class="gas-dropdown-valor"></span>
                             <i class="fas fa-chevron-down gas-dropdown-flecha"></i>`;

        const lista = document.createElement('ul');
        lista.className = 'gas-dropdown-lista';

        for (let n = 0; n <= DIECISEISAVOS; n++) {
            const li = document.createElement('li');
            li.className = 'gas-dropdown-opcion';
            if (PRINCIPALES[n] !== undefined) li.classList.add('es-principal');
            li.dataset.valor = String(n);
            li.textContent = etiqueta(n);
            lista.appendChild(li);
        }

        dd.appendChild(trigger);
        dd.appendChild(lista);
        select.parentNode.insertBefore(dd, select);

        let hint = wrapper.parentNode.querySelector('.gasolina-hint');
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'gasolina-hint';
            hint.innerHTML = '<i class="fas fa-info-circle"></i><span></span>';
            wrapper.parentNode.insertBefore(hint, wrapper.nextSibling);
        }
        const hintTexto = hint.querySelector('span');

        const textoLitros = $id(IDS.litros);

        function pintarHint(n, resaltado) {
            if (hintTexto) hintTexto.textContent = `Equivale a ${enOctavos(n)} · ~${litros(n)} L`;
            hint.classList.toggle('activo', !!resaltado);
        }

        function seleccionar(n) {
            select.value = String(n);
            trigger.querySelector('.gas-dropdown-valor').textContent = `${n}/16`;

            lista.querySelectorAll('.gas-dropdown-opcion').forEach(o => {
                o.classList.toggle('seleccionada', o.dataset.valor === String(n));
            });

            if (textoLitros) textoLitros.textContent = `~${litros(n)} L`;
            pintarHint(n, false);

            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        select._gasSeleccionar = seleccionar;

        function abrir() {
            dd.classList.add('abierto');
            const sel = lista.querySelector('.seleccionada');
            if (sel) sel.scrollIntoView({ block: 'nearest' });
        }

        function cerrar() {
            dd.classList.remove('abierto');
            lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
            pintarHint(parseInt(select.value, 10) || 0, false);
        }

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dd.classList.contains('abierto') ? cerrar() : abrir();
        });

        lista.addEventListener('mouseover', (e) => {
            const op = e.target.closest('.gas-dropdown-opcion');
            if (!op) return;
            lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
            op.classList.add('resaltada');
            pintarHint(parseInt(op.dataset.valor, 10), true);
        });

        lista.addEventListener('mouseleave', () => {
            lista.querySelectorAll('.resaltada').forEach(o => o.classList.remove('resaltada'));
            pintarHint(parseInt(select.value, 10) || 0, false);
        });

        lista.addEventListener('click', (e) => {
            const op = e.target.closest('.gas-dropdown-opcion');
            if (!op) return;
            e.preventDefault();
            e.stopPropagation();
            seleccionar(parseInt(op.dataset.valor, 10));
            cerrar();
        });

        document.addEventListener('click', (e) => {
            if (!dd.contains(e.target)) cerrar();
        });

        trigger.addEventListener('keydown', (e) => {
            const actual = parseInt(select.value, 10) || 0;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                seleccionar(Math.min(DIECISEISAVOS, actual + 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                seleccionar(Math.max(0, actual - 1));
            } else if (e.key === 'Escape') {
                cerrar();
            }
        });

        seleccionar(parseInt(select.value, 10) || CONFIG.gasolinaPorDefecto);
    }

    function setGasolina(n) {
        const select = $id(IDS.select);
        if (!select) return;
        n = parseInt(n, 10);
        if (isNaN(n)) n = CONFIG.gasolinaPorDefecto;
        n = Math.max(0, Math.min(DIECISEISAVOS, n));

        if (typeof select._gasSeleccionar === 'function') {
            select._gasSeleccionar(n);
        } else {
            select.value = String(n);
        }
    }

    function texto(id, valor) {
        const el = $id(id);
        if (el) el.textContent = (valor === null || valor === undefined || valor === '') ? '—' : valor;
    }

    function abrir(vehiculo, opciones) {
        const modal = $id(IDS.modal);
        if (!modal) {
            console.error('[ModalConfirmarVehiculo] Falta el HTML #' + IDS.modal + ' en la vista.');
            return;
        }

        vehiculo = vehiculo || {};
        callbacks = opciones || {};
        vehiculoActual = vehiculo;

        texto(IDS.placas, vehiculo.placas);
        texto(IDS.modelo, vehiculo.modelo);
        texto(IDS.categoria, vehiculo.categoria);
        texto(IDS.color, vehiculo.color);

        const inputKm = $id(IDS.km);
        if (inputKm) {
            inputKm.value = (vehiculo.kilometraje !== undefined && vehiculo.kilometraje !== null)
                ? vehiculo.kilometraje
                : '';
        }

        modal.classList.add('show-modal');
        document.body.style.overflow = 'hidden';

        inicializarDropdownGasolina();
        setGasolina(vehiculo.gasolina !== undefined && vehiculo.gasolina !== null
            ? vehiculo.gasolina
            : CONFIG.gasolinaPorDefecto);

        cargando(false);
    }

    function cerrar() {
        const modal = $id(IDS.modal);
        if (!modal) return;
        modal.classList.remove('show-modal');
        document.body.style.overflow = 'auto';
        cargando(false);
    }

    function cancelar() {
        if (typeof callbacks.onCancelar === 'function') callbacks.onCancelar(vehiculoActual);
        cerrar();
    }

    function cargando(activo) {
        const btn = $id(IDS.btnConfirmar);
        if (!btn) return;
        btn.disabled = !!activo;
        btn.innerHTML = activo
            ? '<i class="fas fa-spinner fa-spin"></i> Guardando...'
            : '<i class="fas fa-check"></i> Confirmar';
    }

    function obtenerDatos() {
        const select = $id(IDS.select);
        const inputKm = $id(IDS.km);

        const gas = parseInt(select ? select.value : CONFIG.gasolinaPorDefecto, 10) || 0;
        const km = inputKm && inputKm.value !== '' ? parseInt(inputKm.value, 10) : null;

        return Object.assign({}, vehiculoActual, {
            gasolina: gas,
            gasolina_texto: `${gas}/16`,
            gasolina_octavos: enOctavos(gas),
            gasolina_litros: litros(gas),
            kilometraje: km
        });
    }

    function validar(datos) {
        if (!CONFIG.exigirKilometraje) return true;

        if (datos.kilometraje === null || isNaN(datos.kilometraje)) {
            notificar('warning', 'Ingresa el kilometraje actual del vehículo.');
            $id(IDS.km)?.focus();
            return false;
        }
        if (datos.kilometraje < 0) {
            notificar('warning', 'El kilometraje no puede ser negativo.');
            $id(IDS.km)?.focus();
            return false;
        }
        return true;
    }

    function confirmar() {
        const datos = obtenerDatos();
        if (!validar(datos)) return;

        if (typeof callbacks.onConfirmar === 'function') {
            callbacks.onConfirmar(datos, { cerrar, cargando, notificar });
        } else {
            cerrar();
        }

        document.dispatchEvent(new CustomEvent('vehiculo:confirmado', { detail: datos }));
    }

    function conectarListeners() {
        if (listenersListos) return;
        listenersListos = true;

        $id(IDS.btnCerrar)?.addEventListener('click', cancelar);
        $id(IDS.btnCancelar)?.addEventListener('click', cancelar);
        $id(IDS.btnConfirmar)?.addEventListener('click', confirmar);

        const modal = $id(IDS.modal);
        modal?.addEventListener('click', (e) => {
            if (CONFIG.cerrarConFondo && e.target === modal) cancelar();
        });

        document.addEventListener('keydown', (e) => {
            if (!CONFIG.cerrarConEsc || e.key !== 'Escape') return;
            if (modal?.classList.contains('show-modal')) cancelar();
        });

        document.addEventListener('click', (e) => {
            const disparador = e.target.closest('[data-confirmar-vehiculo]');
            if (!disparador) return;
            const d = disparador.dataset;
            abrir({
                id: d.idVehiculo,
                placas: d.placas,
                modelo: d.modelo,
                categoria: d.categoria,
                color: d.color,
                gasolina: d.gasolina,
                kilometraje: d.kilometraje
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', conectarListeners);
    } else {
        conectarListeners();
    }

    global.ModalConfirmarVehiculo = {
        abrir,
        cerrar,
        cargando,
        obtenerDatos,
        setGasolina,
        litros,
        enOctavos,
        config: CONFIG
    };

    global.inicializarDropdownGasolina = inicializarDropdownGasolina;

})(window);

/* ==============================
   ENGANCHE APARTAR AUTO
============================== */
(function engancharConfirmarVehiculoEnApartar() {
    'use strict';

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

    const IGNORAR = 'button, input, select, textarea, a, .celda-editable, .btn-edit-inline';

    async function guardarInventario(idVehiculo, campo, valor) {
        const res = await fetch('/admin/vehiculo/actualizar-inventario', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ id_vehiculo: idVehiculo, campo, valor })
        });
        const data = await res.json();
        if (!res.ok || !(data.success || data.ok)) {
            throw new Error(data.error || `No se pudo actualizar ${campo}`);
        }
        return data;
    }

    function refrescarFila(fila, campo, valor, litros) {
        if (!fila) return;
        if (campo === 'gasolina') {
            const span = fila.querySelector('[data-tipo="gas"] .celda-valor');
            if (span) span.textContent = `${valor}/16`;
            fila.dataset.gasOriginal = valor;
            const cl = fila.querySelector('.celda-litros');
            if (cl && litros !== undefined && litros !== null) cl.textContent = litros;
        } else {
            const span = fila.querySelector('[data-tipo="km"] .celda-valor');
            if (span) span.textContent = Number(valor).toLocaleString();
            fila.dataset.kmOriginal = valor;
        }
    }

    document.addEventListener('click', (e) => {

        if (e.target.closest(IGNORAR)) return;

        const fila = e.target.closest('#tablaVehiculos tr');
        if (!fila || !fila.dataset.idVehiculo) return;

        const gasOriginal = parseInt(fila.dataset.gasOriginal, 10) || 0;
        const kmOriginal = parseInt(fila.dataset.kmOriginal, 10) || 0;

        ModalConfirmarVehiculo.abrir({
            id:          fila.dataset.idVehiculo,
            placas:      fila.dataset.placa,
            modelo:      fila.cells[3]?.textContent.trim(),
            categoria:   fila.dataset.categoria,
            color:       fila.dataset.color,
            gasolina:    gasOriginal,
            kilometraje: kmOriginal
        }, {
            onConfirmar: async (datos, api) => {
                api.cargando(true);
                OverlayActualizacion.cargando();

                try {
                    if (datos.gasolina !== gasOriginal) {
                        const r = await guardarInventario(datos.id, 'gasolina', datos.gasolina);
                        refrescarFila(fila, 'gasolina', datos.gasolina, r.litros);
                    }
                    if (datos.kilometraje !== kmOriginal) {
                        await guardarInventario(datos.id, 'kilometraje', datos.kilometraje);
                        refrescarFila(fila, 'kilometraje', datos.kilometraje);
                    }

                    const btnSeleccionar = fila.querySelector('.btn-select-auto');
                    if (!btnSeleccionar) throw new Error('No se encontró el botón Seleccionar de la fila');
                    btnSeleccionar.click();

                } catch (err) {
                    console.error(err);
                    OverlayActualizacion.ocultar();
                    api.cargando(false);
                    api.notificar('error', err.message || 'No se pudo continuar');
                }
            }
        });
    });

})();
