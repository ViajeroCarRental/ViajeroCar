/* ============================================================
   DROPOFF Y DELIVERY — lógica de la vista
============================================================ */
(function () {
    "use strict";

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const BASE = window.DROPOFF_BASE_URL || "/admin/dropoff";

    /* Parche: cerrar dialogs antes de cualquier Swal */
    const _swalFire = Swal.fire.bind(Swal);
    Swal.fire = function (...args) {
        document.querySelectorAll('dialog[open]').forEach(d => d.close());
        return _swalFire(...args);
    };

    /* Helper POST con FormData */
    function postForm(url, dataObj) {
        const form = new FormData();
        form.append('_token', CSRF);
        Object.keys(dataObj).forEach(k => form.append(k, dataObj[k]));
        return fetch(url, { method: 'POST', body: form }).then(r => r.json());
    }

    function errorSwal() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud.',
            confirmButtonText: 'Entendido'
        });
    }

    /* =========================================================
       TABS
    ========================================================= */
    window.switchTab = function (tabId, btnElement) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btnElement.classList.add('active');
    };

    /* =========================================================
       LLENAR FILTROS DE ORIGEN Y DESTINO (desde las filas)
    ========================================================= */
    function initFiltros() {
        const selectOrigen = document.getElementById('filtroOrigen');
        const selectDestino = document.getElementById('filtroDestino');
        if (!selectOrigen || !selectDestino) return;

        const filas = document.querySelectorAll('.fila-viaje');
        const origenes = new Set();
        const destinos = new Set();

        filas.forEach(fila => {
            if (fila.dataset.origen)  origenes.add(fila.dataset.origen);
            if (fila.dataset.destino) destinos.add(fila.dataset.destino);
        });

        const titular = (txt) => txt.split(' ')
            .map(w => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');

        origenes.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o;
            opt.text = titular(o);
            selectOrigen.appendChild(opt);
        });
        destinos.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d;
            opt.text = titular(d);
            selectDestino.appendChild(opt);
        });
    }

    /* =========================================================
       FILTRO + SIMULADOR DE PRECIO (agrupado por ciudad)
    ========================================================= */
    window.aplicarFiltros = function () {
        const valOrigen = document.getElementById('filtroOrigen').value;
        const valDestino = document.getElementById('filtroDestino').value;
        const selectCat = document.getElementById('filtroCategoria');
        const costoPorKm = parseFloat(selectCat.options[selectCat.selectedIndex].dataset.costokm) || 0;

        let totalVisibles = 0;

        document.querySelectorAll('.ciudad-bloque').forEach(bloque => {
            let visiblesEnBloque = 0;

            bloque.querySelectorAll('.fila-viaje').forEach(fila => {
                const origenFila = fila.dataset.origen;
                const destinoFila = fila.dataset.destino;
                const celdaCosto = fila.querySelector('.celda-costo');

                const mostrarOrigen  = (valOrigen === "todos"  || origenFila === valOrigen);
                const mostrarDestino = (valDestino === "todos" || destinoFila === valDestino);
                const visible = mostrarOrigen && mostrarDestino;

                fila.style.display = visible ? "table-row" : "none";
                if (visible) visiblesEnBloque++;

                if (costoPorKm === 0) {
                    celdaCosto.innerText = "Seleccione auto...";
                    celdaCosto.style.color = "#94a3b8";
                    celdaCosto.style.fontWeight = "normal";
                } else {
                    const total = (parseFloat(fila.dataset.km) || 0) * costoPorKm;
                    celdaCosto.innerText = "$" + total.toFixed(2);
                    celdaCosto.style.color = "#b22222";
                    celdaCosto.style.fontWeight = "900";
                }
            });

            bloque.style.display = visiblesEnBloque > 0 ? "" : "none";
            totalVisibles += visiblesEnBloque;
        });

        const sinRutas = document.getElementById('sinRutas');
        if (sinRutas) sinRutas.style.display = totalVisibles === 0 ? 'block' : 'none';
    };

    /* =========================================================
       SELECTOR DE DESTINOS (checkboxes + "Todas" + km + visibilidad)
    ========================================================= */

    // Guarda los destinos elegidos: { destino, estado }
    let destinosSeleccionados = [];

    function initSelectorDestinos() {
        const selectOrigen  = document.getElementById('ub_id_ciudad_origen');
        const btnAbrir      = document.getElementById('btnAbrirDestinos');
        const hintOrigen    = document.getElementById('ubHintOrigen');
        const modalDest     = document.getElementById('modalDestinos');
        const chkTodas      = document.getElementById('ub_check_todas');
        const inputBuscar   = document.getElementById('ub_buscar_destino');
        const btnConfirmar  = document.getElementById('btnConfirmarDestinos');
        const btnNuevoDest  = document.getElementById('btnAgregarDestinoNuevo');
        const lista         = document.getElementById('ub_lista_destinos');

        if (!selectOrigen || !btnAbrir || !modalDest) return;

        // Habilitar el botón "Seleccionar destinos" solo con origen elegido
        selectOrigen.addEventListener('change', function () {
            const hayOrigen = !!this.value;
            btnAbrir.disabled = !hayOrigen;
            btnAbrir.style.opacity = hayOrigen ? '1' : '.5';
            btnAbrir.style.cursor  = hayOrigen ? 'pointer' : 'not-allowed';
            if (hintOrigen) hintOrigen.style.display = hayOrigen ? 'none' : 'block';
        });

        // Abrir el modal de destinos
        btnAbrir.addEventListener('click', function () {
            if (btnAbrir.disabled) return;
            modalDest.showModal();
        });

        // Check "Todas": marca/desmarca todos los visibles
        if (chkTodas) {
            chkTodas.addEventListener('change', function () {
                const marcar = this.checked;
                lista.querySelectorAll('.destino-item').forEach(item => {
                    if (item.style.display === 'none') return; // respeta el buscador
                    const chk = item.querySelector('.chk-destino');
                    if (chk) chk.checked = marcar;
                });
            });
        }

        // Buscador en vivo
        if (inputBuscar) {
            inputBuscar.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                lista.querySelectorAll('.destino-item').forEach(item => {
                    const nombre = item.dataset.nombre || '';
                    item.style.display = nombre.includes(q) ? 'flex' : 'none';
                });
            });
        }

        // Agregar un destino nuevo a la lista (queda marcado)
        if (btnNuevoDest) {
            btnNuevoDest.addEventListener('click', function () {
                const nombreInput = document.getElementById('ub_nuevo_destino_nombre');
                const estadoInput = document.getElementById('ub_nuevo_destino_estado');
                const nombre = nombreInput.value.trim();
                const estado = estadoInput.value.trim();

                if (!nombre) {
                    Swal.fire({ icon: 'warning', title: 'Falta el nombre', text: 'Escribe el nombre del destino.' });
                    return;
                }

                // Evitar duplicados por nombre
                const yaExiste = Array.from(lista.querySelectorAll('.chk-destino'))
                    .some(c => (c.value || '').toLowerCase() === nombre.toLowerCase());

                if (yaExiste) {
                    Swal.fire({ icon: 'info', title: 'Ya existe', text: 'Ese destino ya está en la lista.' });
                    return;
                }

                const label = document.createElement('label');
                label.className = 'check-vis destino-item';
                label.dataset.nombre = nombre.toLowerCase();
                label.style.cssText = 'display:flex; align-items:center; gap:10px; padding:6px 0;';
                label.innerHTML =
                    '<input type="checkbox" class="chk-destino" checked' +
                    ' value="' + nombre.replace(/"/g, '&quot;') + '"' +
                    ' data-destino="' + nombre.replace(/"/g, '&quot;') + '"' +
                    ' data-estado="' + estado.replace(/"/g, '&quot;') + '">' +
                    '<span>' + nombre +
                    ' <small style="color:#94a3b8;">' + (estado ? '— ' + estado : '') + '</small></span>';

                lista.prepend(label);
                nombreInput.value = '';
                estadoInput.value = '';
            });
        }

        // Confirmar selección → guardar y pintar filas de km/visibilidad
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function () {
                destinosSeleccionados = [];
                lista.querySelectorAll('.chk-destino:checked').forEach(chk => {
                    destinosSeleccionados.push({
                        destino: chk.dataset.destino || chk.value,
                        estado:  chk.dataset.estado || ''
                    });
                });

                if (destinosSeleccionados.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Sin destinos', text: 'Marca al menos un destino.' });
                    return;
                }

                pintarRutasSeleccionadas();
                modalDest.close();
            });
        }
    }

    // Pinta en #ub_rutas_wrap una tarjeta por destino: km + visibilidad (desactivada)
    function pintarRutasSeleccionadas() {
        const wrap = document.getElementById('ub_rutas_wrap');
        if (!wrap) return;
        wrap.innerHTML = '';

        const titulo = document.createElement('div');
        titulo.style.cssText = 'font-weight:700; color:#0f172a; margin-bottom:10px;';
        titulo.textContent = 'Destinos seleccionados (' + destinosSeleccionados.length + ')';
        wrap.appendChild(titulo);

        destinosSeleccionados.forEach((d, i) => {
            const card = document.createElement('div');
            card.className = 'ruta-item-card';
            card.dataset.destino = d.destino;
            card.dataset.estado  = d.estado;
            card.style.cssText =
                'border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:10px; background:#f8fafc;';

            card.innerHTML =
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">' +
                    '<strong style="color:#0f172a;">' + d.destino +
                        (d.estado ? ' <small style="color:#94a3b8; font-weight:normal;">— ' + d.estado + '</small>' : '') +
                    '</strong>' +
                    '<button type="button" class="btn-icon btn-del btn-quitar-ruta" data-idx="' + i + '">🗑️</button>' +
                '</div>' +
                '<label class="label" style="margin-top:0;">Distancia (km)</label>' +
                '<input type="number" class="input mono ruta-km" step="0.1" min="0" required placeholder="Ej: 320">' +
                '<div class="visibilidad-grid" style="margin-top:10px;">' +
                    '<label class="check-vis">' +
                        '<input type="checkbox" class="ruta-ver-usuario">' +
                        '<span>Permitir ver en página web (usuario)</span>' +
                    '</label>' +
                    '<label class="check-vis">' +
                        '<input type="checkbox" class="ruta-ver-admin">' +
                        '<span>Permitir ver en panel (admin)</span>' +
                    '</label>' +
                '</div>';

            wrap.appendChild(card);
        });

        // Quitar una tarjeta de destino
        wrap.querySelectorAll('.btn-quitar-ruta').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.dataset.idx, 10);
                destinosSeleccionados.splice(idx, 1);
                if (destinosSeleccionados.length === 0) {
                    wrap.innerHTML = '';
                } else {
                    pintarRutasSeleccionadas();
                }
            });
        });
    }

    /* =========================================================
       CREAR RUTAS (alta masiva: 1 origen → varios destinos)
    ========================================================= */
    function initFormUbicacion() {
        const form = document.getElementById('formUbicacion');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const idCiudad = document.getElementById('ub_id_ciudad_origen').value;
            if (!idCiudad) {
                Swal.fire({ icon: 'warning', title: 'Falta el origen', text: 'Elige la ciudad de origen.' });
                return;
            }

            const cards = document.querySelectorAll('#ub_rutas_wrap .ruta-item-card');
            if (cards.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Sin destinos', text: 'Selecciona al menos un destino.' });
                return;
            }

            const rutas = [];
            let faltanKm = false;

            cards.forEach(card => {
                const km = card.querySelector('.ruta-km').value;
                if (!km) { faltanKm = true; return; }

                rutas.push({
                    destino:     card.dataset.destino,
                    estado:      card.dataset.estado || '',
                    km:          km,
                    ver_usuario: card.querySelector('.ruta-ver-usuario').checked ? 1 : 0,
                    ver_admin:   card.querySelector('.ruta-ver-admin').checked ? 1 : 0
                });
            });

            if (faltanKm) {
                Swal.fire({ icon: 'warning', title: 'Faltan kilómetros', text: 'Captura el km de cada destino seleccionado.' });
                return;
            }

            // Enviar como rutas[i][campo] para que Laravel lo reciba como arreglo
            const payload = { id_ciudad_origen: idCiudad };
            rutas.forEach((r, i) => {
                payload['rutas[' + i + '][destino]']     = r.destino;
                payload['rutas[' + i + '][estado]']      = r.estado;
                payload['rutas[' + i + '][km]']          = r.km;
                payload['rutas[' + i + '][ver_usuario]'] = r.ver_usuario;
                payload['rutas[' + i + '][ver_admin]']   = r.ver_admin;
            });

            postForm(BASE + "/ubicacion", payload)
            .then(resp => {
                if (resp && resp.success === false) {
                    Swal.fire({ icon: 'warning', title: 'Aviso', text: resp.message || 'No se registraron rutas.' });
                    return;
                }
                const n = (resp && resp.creadas) ? resp.creadas : rutas.length;
                Swal.fire({
                    icon: 'success', title: 'Rutas creadas',
                    text: 'Se registraron ' + n + ' ruta(s) correctamente.',
                    confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                }).then(() => location.reload());
            })
            .catch(() => errorSwal());
        });
    }

    /* =========================================================
       EDITAR (origen + km + visibilidad; destino y estado bloqueados)
    ========================================================= */
    window.abrirEditarKm = function (id, destino, estado, kmActual, idCiudadOrigen, verUsuario, verAdmin) {
        document.getElementById('ek_id').value = id;
        document.getElementById('ek_destino').value = destino || '';
        document.getElementById('ek_estado').value = estado || '';
        document.getElementById('ek_km').value = kmActual;

        const selOrigen = document.getElementById('ek_id_ciudad_origen');
        selOrigen.value = (idCiudadOrigen !== null && idCiudadOrigen !== undefined && idCiudadOrigen !== 'null')
            ? String(idCiudadOrigen)
            : '';

        document.getElementById('ek_ver_usuario').checked = Number(verUsuario) === 1;
        document.getElementById('ek_ver_admin').checked   = Number(verAdmin) === 1;

        document.getElementById('modalEditarKm').showModal();
    };

    function initFormEditarKm() {
        const form = document.getElementById('formEditarKm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('ek_id').value;
            const km = document.getElementById('ek_km').value;
            const idCiudad = document.getElementById('ek_id_ciudad_origen').value;
            const verUsuario = document.getElementById('ek_ver_usuario').checked ? 1 : 0;
            const verAdmin   = document.getElementById('ek_ver_admin').checked ? 1 : 0;

            if (!idCiudad) {
                Swal.fire({ icon: 'warning', title: 'Falta el origen', text: 'Selecciona la ciudad de origen.' });
                return;
            }

            document.getElementById('modalEditarKm').close();

            postForm(BASE + "/update-km", {
                id: id,
                km: km,
                id_ciudad_origen: idCiudad,
                ver_usuario: verUsuario,
                ver_admin: verAdmin
            })
            .then(() => {
                Swal.fire({
                    icon: 'success', title: 'Ruta actualizada',
                    text: 'Los datos se modificaron correctamente.',
                    confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                }).then(() => location.reload());
            })
            .catch(() => errorSwal());
        });
    }

    /* =========================================================
       EDITAR COSTO POR CATEGORÍA (intacto)
    ========================================================= */
    window.openEditCategoria = function (id, nombre, costoActual) {
        document.getElementById('c_id_categoria').value = id;
        document.getElementById('c_nombre').value = nombre;
        document.getElementById('c_costo').value = costoActual;
        document.getElementById('modalCosto').showModal();
    };

    function initFormCosto() {
        const form = document.getElementById('formCosto');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const nombre = document.getElementById('c_nombre').value;
            document.getElementById('modalCosto').close();

            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Se modificará el costo por km de "' + nombre + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, modificar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) {
                    document.getElementById('modalCosto').showModal();
                    return;
                }
                postForm(BASE + "/update-costo", {
                    id_categoria: document.getElementById('c_id_categoria').value,
                    costo_km:     document.getElementById('c_costo').value
                })
                .then(() => {
                    Swal.fire({
                        icon: 'success', title: 'Costo actualizado',
                        text: 'El costo por km se modificó correctamente.',
                        confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                    }).then(() => location.reload());
                })
                .catch(() => errorSwal());
            });
        });
    }

    /* =========================================================
       ELIMINAR RUTA
    ========================================================= */
    window.confirmarEliminar = function (id, nombre) {
        Swal.fire({
            title: '¿Eliminar ruta?',
            text: 'Se eliminará "' + nombre + '". Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            postForm(BASE + "/ubicacion/eliminar/" + id, {})
            .then(() => {
                Swal.fire({
                    icon: 'success', title: 'Ruta eliminada',
                    text: 'Se eliminó correctamente.',
                    confirmButtonText: 'Aceptar', confirmButtonColor: '#10b981'
                }).then(() => location.reload());
            })
            .catch(() => errorSwal());
        });
    };

    /* =========================================================
       BOOT
    ========================================================= */
    document.addEventListener('DOMContentLoaded', function () {
        initFiltros();
        initSelectorDestinos();
        initFormUbicacion();
        initFormEditarKm();
        initFormCosto();
    });

})();
