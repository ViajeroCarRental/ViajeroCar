/* ============================================================
   OFICINAS — lógica de la vista
   - Compresión de imágenes en navegador (browser-image-compression)
   - Select de ciudad escribible (Select2, autocargado si falta)
   - Filtro de búsqueda en vivo
   - Modales nueva / editar / eliminar / galería (SweetAlert2)
   - Horario con selects de hora + check 24/7
============================================================ */
(function () {
    "use strict";

    const BASE_URL = window.OFICINAS_BASE_URL || "/oficinas";

    /* =========================================================
       PARCHE: cerrar <dialog> antes de cualquier Swal
    ========================================================= */
    if (window.Swal) {
        const _fire = Swal.fire.bind(Swal);
        Swal.fire = function (...args) {
            document.querySelectorAll('dialog[open]').forEach(d => d.close());
            return _fire(...args);
        };
    }

    /* =========================================================
       COMPRESIÓN DE IMÁGENES → base64 en hidden
    ========================================================= */
    async function comprimirYGuardar(file, targetHiddenId, previewEl) {
        if (!file) return;

        if (!file.type.startsWith("image/")) {
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Archivo inválido', text: 'Solo se permiten imágenes.' });
            return;
        }

        const opciones = {
            maxSizeMB: 0.6,
            maxWidthOrHeight: 1600,
            useWebWorker: true,
            initialQuality: 0.8,
        };

        try {
            let resultado = file;
            if (window.imageCompression) {
                resultado = await window.imageCompression(file, opciones);
            }

            const dataUrl = window.imageCompression
                ? await imageCompression.getDataUrlFromFile(resultado)
                : await leerArchivoComoDataUrl(resultado);

            document.getElementById(targetHiddenId).value = dataUrl;

            if (previewEl) {
                previewEl.src = dataUrl;
                previewEl.style.display = "block";
            }
        } catch (err) {
            console.error("Error al comprimir imagen:", err);
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo procesar la imagen.' });
        }
    }

    function leerArchivoComoDataUrl(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function initFotos() {
        document.querySelectorAll(".foto-input").forEach(input => {
            input.addEventListener("change", function () {
                const targetId = this.dataset.target;
                const previewId = targetId.replace("imagen", "preview");
                const preview = document.getElementById(previewId);
                const file = this.files && this.files[0];
                comprimirYGuardar(file, targetId, preview);
            });
        });
    }

    /* =========================================================
       HORARIO  (selects de día + hora + check 24/7)
    ========================================================= */
    function poblarHoras(selectId) {
        const sel = document.getElementById(selectId);
        if (!sel || sel.dataset.poblado === '1') return;
        sel.dataset.poblado = '1';
        for (let h = 0; h < 24; h++) {
            const hh = String(h).padStart(2, '0') + ':00';
            const opt = document.createElement('option');
            opt.value = hh;
            opt.textContent = hh;
            sel.appendChild(opt);
        }
    }

    function vincularHorario(prefijo) {
        poblarHoras(`${prefijo}_hora_inicio`);
        poblarHoras(`${prefijo}_hora_fin`);

        const check24 = document.getElementById(`${prefijo}_24h`);
        const schedule = document.getElementById(`${prefijo}_schedule`);
        const diaI = document.getElementById(`${prefijo}_dia_inicio`);
        const diaF = document.getElementById(`${prefijo}_dia_fin`);
        const horaI = document.getElementById(`${prefijo}_hora_inicio`);
        const horaF = document.getElementById(`${prefijo}_hora_fin`);
        const hidden = document.getElementById(`${prefijo}_horario_hidden`);
        if (!hidden) return;

        const actualizar = () => {
            if (check24 && check24.checked) {
                hidden.value = '24/7';
                if (schedule) schedule.classList.add('is-disabled');
            } else {
                if (schedule) schedule.classList.remove('is-disabled');
                hidden.value =
                    `${diaI.value} A ${diaF.value} ${horaI.value} - ${horaF.value}`.toUpperCase();
            }
        };

        [check24, diaI, diaF, horaI, horaF].forEach(el => {
            if (el) {
                el.addEventListener('change', actualizar);
                el.addEventListener('input', actualizar);
            }
        });
        actualizar();
    }

    /* =========================================================
       ABRIR / EDITAR
    ========================================================= */
    window.abrirEditar = function (sucursal, horario) {
        document.getElementById('edit_id_ciudad').value = sucursal.id_ciudad;
        document.getElementById('edit_nombre').value = sucursal.nombre;
        document.getElementById('edit_direccion').value = sucursal.direccion;
        document.getElementById('edit_telefono').value = sucursal.telefono ?? '';
        document.getElementById('edit_url_direccion').value = sucursal.url_direccion ?? '';

        // Banderas de visibilidad
        document.getElementById('edit_ver_usuario').checked = !!Number(sucursal.ver_usuario);
        document.getElementById('edit_ver_admin').checked   = !!Number(sucursal.ver_admin);

        // Limpiar previews y hidden de imágenes
        ['edit_imagen_1', 'edit_imagen_2'].forEach(id => {
            const h = document.getElementById(id);
            if (h) h.value = '';
        });
        ['edit_preview_1', 'edit_preview_2'].forEach(id => {
            const p = document.getElementById(id);
            if (p) { p.src = ''; p.style.display = 'none'; }
        });

        // Mostrar foto actual si existe
        if (Number(sucursal.tiene_imagen_1)) {
            const p1 = document.getElementById('edit_preview_1');
            if (p1) { p1.src = `${BASE_URL}/${sucursal.id_sucursal}/imagen/1?t=${Date.now()}`; p1.style.display = 'block'; }
        }
        if (Number(sucursal.tiene_imagen_2)) {
            const p2 = document.getElementById('edit_preview_2');
            if (p2) { p2.src = `${BASE_URL}/${sucursal.id_sucursal}/imagen/2?t=${Date.now()}`; p2.style.display = 'block'; }
        }

        // Parsear horario: "24/7" o "DIA A DIA HH:MM - HH:MM"
        const check24 = document.getElementById('edit_24h');
        if (horario && horario.trim().toUpperCase() === '24/7') {
            if (check24) check24.checked = true;
        } else if (horario) {
            if (check24) check24.checked = false;
            const m = horario.match(/^(\S+)\s+A\s+(\S+)\s+(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/i);
            if (m) {
                document.getElementById('edit_dia_inicio').value  = m[1].toUpperCase();
                document.getElementById('edit_dia_fin').value     = m[2].toUpperCase();
                document.getElementById('edit_hora_inicio').value = m[3];
                document.getElementById('edit_hora_fin').value    = m[4];
            }
        }
        // recalcular hidden y estado visual del schedule
        if (check24) check24.dispatchEvent(new Event('change'));

        document.getElementById('formEditar').action = `${BASE_URL}/${sucursal.id_sucursal}`;

        // Reflejar ciudad en Select2 si está activo
        if (window.jQuery && jQuery('#edit_id_ciudad').data('select2')) {
            jQuery('#edit_id_ciudad').val(sucursal.id_ciudad).trigger('change.select2');
        }

        document.getElementById('modalEditar').showModal();
    };

    /* Confirmar guardado de edición */
    function bindFormEditar() {
        const formEditar = document.getElementById('formEditar');
        if (!formEditar) return;

        formEditar.addEventListener('submit', function (e) {
            e.preventDefault();

            // Asegura que el hidden del horario esté al día
            const check24 = document.getElementById('edit_24h');
            if (check24) check24.dispatchEvent(new Event('change'));

            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Se modificará la oficina "' + document.getElementById('edit_nombre').value + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, modificar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) formEditar.submit();
            });
        });
    }

    /* =========================================================
       ELIMINAR
    ========================================================= */
    window.confirmarEliminar = function (id, nombre) {
        Swal.fire({
            title: '¿Eliminar oficina?',
            text: 'Se eliminará la sucursal "' + nombre + '". Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('formEliminar');
                form.action = `${BASE_URL}/${id}`;
                form.submit();
            }
        });
    };

    /* =========================================================
       GALERÍA DE IMÁGENES
    ========================================================= */
    window.verImagenes = function (id, nombre, tiene1, tiene2) {
        const cont = document.getElementById('galeria_contenido');
        const titulo = document.getElementById('galeria_titulo');
        if (titulo) titulo.textContent = 'Imágenes — ' + nombre;
        cont.innerHTML = '';

        let agregadas = 0;
        if (tiene1) {
            const img = document.createElement('img');
            img.src = `${BASE_URL}/${id}/imagen/1?t=${Date.now()}`;
            cont.appendChild(img);
            agregadas++;
        }
        if (tiene2) {
            const img = document.createElement('img');
            img.src = `${BASE_URL}/${id}/imagen/2?t=${Date.now()}`;
            cont.appendChild(img);
            agregadas++;
        }
        if (agregadas === 0) {
            const aviso = document.createElement('div');
            aviso.className = 'galeria-sin-fotos';
            aviso.textContent = 'Esta oficina no tiene fotos cargadas.';
            cont.appendChild(aviso);
        }

        document.getElementById('modalGaleria').showModal();
    };

    /* =========================================================
       FILTRO DE BÚSQUEDA EN VIVO
    ========================================================= */
    function initBuscador() {
        const buscador = document.getElementById('buscadorOficina');
        if (!buscador) return;

        buscador.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let totalVisibles = 0;

            document.querySelectorAll('.ciudad-bloque').forEach(bloque => {
                let visiblesEnBloque = 0;
                bloque.querySelectorAll('.fila-oficina').forEach(fila => {
                    const nombre = fila.dataset.nombre || '';
                    const ciudad = fila.dataset.ciudad || '';
                    const coincide = !q || nombre.includes(q) || ciudad.includes(q);
                    fila.style.display = coincide ? '' : 'none';
                    if (coincide) visiblesEnBloque++;
                });
                bloque.style.display = visiblesEnBloque > 0 ? '' : 'none';
                totalVisibles += visiblesEnBloque;
            });

            const sinRes = document.getElementById('sinResultados');
            if (sinRes) sinRes.style.display = totalVisibles === 0 ? 'block' : 'none';
        });
    }

    /* =========================================================
       SOLO NÚMEROS EN TELÉFONO
    ========================================================= */
    function initSoloNumeros() {
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('only-numbers')) {
                e.target.value = e.target.value.replace(/\D/g, '');
            }
        });
    }

    /* =========================================================
       BOTÓN NUEVA OFICINA
    ========================================================= */
    function initBotonNueva() {
        const btn = document.getElementById('btnNuevaOficina');
        if (btn) btn.addEventListener('click', () => document.getElementById('modalNueva').showModal());
    }

    /* =========================================================
       SELECT2 (escribible). Autocarga jQuery + Select2 si faltan.
    ========================================================= */
    function aplicarSelect2() {
        if (!window.jQuery || !jQuery.fn.select2) return;
        jQuery('.select-ciudad').each(function () {
            jQuery(this).select2({
                placeholder: 'Seleccione o escriba una ciudad...',
                width: '100%',
                dropdownParent: jQuery(this).closest('dialog')
            });
        });
    }

    function cargarScript(src) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    async function initSelectCiudad() {
        try {
            if (!window.jQuery) {
                await cargarScript('https://code.jquery.com/jquery-3.6.0.min.js');
            }
            if (!jQuery.fn.select2) {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                document.head.appendChild(css);
                await cargarScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
            }
            aplicarSelect2();
        } catch (e) {
            console.warn('Select2 no disponible; se usa select normal.', e);
        }
    }

    /* =========================================================
       TOASTS DE SESIÓN
    ========================================================= */
    function initFlash() {
        if (window.OFICINAS_FLASH && window.Swal) {
            const f = window.OFICINAS_FLASH;
            if (f.success) Swal.fire({ icon: 'success', title: 'Listo', text: f.success, confirmButtonColor: '#10b981' });
            else if (f.error) Swal.fire({ icon: 'error', title: 'Error', text: f.error });
            else if (f.errors && f.errors.length) Swal.fire({ icon: 'error', title: 'Revisa el formulario', html: f.errors.join('<br>') });
        }
    }

    /* =========================================================
       BOOT
    ========================================================= */
    document.addEventListener('DOMContentLoaded', function () {
        vincularHorario('nueva');
        vincularHorario('edit');
        bindFormEditar();
        initFotos();
        initBuscador();
        initSoloNumeros();
        initBotonNueva();
        initSelectCiudad();
        initFlash();
    });

})();
