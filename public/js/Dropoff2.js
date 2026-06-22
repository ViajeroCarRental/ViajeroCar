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
       AUTOLLENADO DE ESTADO AL ELEGIR DESTINO EXISTENTE
    ========================================================= */
    function initDestinoAutollenado() {
        const selectDestino = document.getElementById('ub_destino_select');
        const inputDestino  = document.getElementById('ub_destino');
        const inputEstado   = document.getElementById('ub_estado');
        const wrapNuevo     = document.getElementById('ub_destino_nuevo_wrap');
        if (!selectDestino) return;

        selectDestino.addEventListener('change', function () {
            if (this.value === '__nuevo__') {
                if (wrapNuevo) wrapNuevo.style.display = 'block';
                if (inputDestino) { inputDestino.value = ''; inputDestino.required = true; }
                if (inputEstado)  { inputEstado.value = ''; inputEstado.readOnly = false; }
            } else if (this.value) {
                const opt = this.options[this.selectedIndex];
                if (wrapNuevo) wrapNuevo.style.display = 'none';
                if (inputDestino) { inputDestino.value = opt.dataset.destino || this.value; inputDestino.required = false; }
                if (inputEstado)  { inputEstado.value = opt.dataset.estado || ''; inputEstado.readOnly = true; }
            } else {
                if (wrapNuevo) wrapNuevo.style.display = 'none';
                if (inputDestino) { inputDestino.value = ''; inputDestino.required = false; }
                if (inputEstado)  { inputEstado.value = ''; inputEstado.readOnly = false; }
            }
        });
    }

    /* =========================================================
       CREAR RUTA
    ========================================================= */
    function initFormUbicacion() {
        const form = document.getElementById('formUbicacion');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const idCiudad = document.getElementById('ub_id_ciudad_origen').value;
            const destino  = document.getElementById('ub_destino').value.trim();
            const estado   = document.getElementById('ub_estado').value.trim();
            const km       = document.getElementById('ub_km').value;
            const verUsuario = document.getElementById('ub_ver_usuario').checked ? 1 : 0;
            const verAdmin   = document.getElementById('ub_ver_admin').checked ? 1 : 0;

            if (!idCiudad || !destino || !km) {
                Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Elige ciudad de origen, destino y km.' });
                return;
            }

            postForm(BASE + "/ubicacion", {
                id_ciudad_origen: idCiudad,
                destino: destino,
                estado: estado,
                km: km,
                ver_usuario: verUsuario,
                ver_admin: verAdmin
            })
            .then(() => {
                Swal.fire({
                    icon: 'success', title: 'Ruta creada',
                    text: 'La ruta se registró correctamente.',
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
        initDestinoAutollenado();
        initFormUbicacion();
        initFormEditarKm();
        initFormCosto();
    });

})();
