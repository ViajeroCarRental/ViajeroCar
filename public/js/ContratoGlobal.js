/**
 * ContratoGlobal.js
 * Lógica compartida para la Gestión de Contratos (Pasos 1 al 6)
 */

// ─────────────────────────────────────────────
// STORE (sessionStorage wrapper)
// ─────────────────────────────────────────────

window.ContratoStore = {
    set: (key, value) => sessionStorage.setItem('global_contrato_' + key, JSON.stringify(value)),
    get: (key, def = null) => { const v = sessionStorage.getItem('global_contrato_' + key); return v ? JSON.parse(v) : def; },
    clear: () => Object.keys(sessionStorage).filter(k => k.startsWith('global_contrato_')).forEach(k => sessionStorage.removeItem(k)),
};

// ─────────────────────────────────────────────
// UTILIDADES DOM Y FORMATO
// ─────────────────────────────────────────────

window.$ = (s) => document.querySelector(s);
window.$$ = (s) => Array.from(document.querySelectorAll(s));

window.formatPhone = (val) => {
    if (!val) return '—';
    const n = val.toString().replace(/\D/g, '');
    if (n.length === 12) return `+52 (${n.slice(2, 5)}) ${n.slice(5, 8)}-${n.slice(8)}`;
    if (n.length === 10) return `(${n.slice(0, 3)}) ${n.slice(3, 6)}-${n.slice(6)}`;
    return val;
};

window.money = (amount) => {
    const n = parseFloat(amount) || 0;
    return '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MXN';
};

window.listaVehiculosOriginal = [];

// ─────────────────────────────────────────────
// STEPPER
// ─────────────────────────────────────────────

window.actualizarStepper = (pasoActual) => {
    const lines = window.$$('.stepper-line');

    window.$$('.stepper-item').forEach((item, i) => {
        const paso = parseInt(item.getAttribute('data-step-indicator'));
        item.classList.remove('active', 'completed');
        if (lines[i]) lines[i].classList.remove('completed');

        if (paso === pasoActual) item.classList.add('active');
        else if (paso < pasoActual) {
            item.classList.add('completed');
            if (lines[i]) lines[i].classList.add('completed');
        }
    });
};

// ─────────────────────────────────────────────
// IMÁGENES POR CATEGORÍA (local fallback)
// ─────────────────────────────────────────────

const CAT_IMAGES = {
    C: '/img/aveo.webp', D: '/img/virtus.webp', E: '/img/jetta.webp',
    F: '/img/camry.webp', IC: '/img/renegade.webp', I: '/img/taos.webp',
    IB: '/img/avanza.webp', M: '/img/Odyssey.webp', L: '/img/Hiace.webp',
    H: '/img/Frontier.webp', HI: '/img/Tacoma.webp',
};
const IMG_FALLBACK = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAQAAADwXcorAAAAeUlEQVR42u3PAQ0AAAgDoJvYv7Y6uI0LAtI6S0hISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISEhISGg7CiwA98X9m8YAAAAASUVORK5CYII=';

const getLocalImg = (codigo) => CAT_IMAGES[codigo] || '/img/Logotipo.png';

// ─────────────────────────────────────────────
// DOM READY
// ─────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const contratoApp = document.getElementById('contratoApp') || document.getElementById('contratoInicial');
    window.ID_CONTRATO = contratoApp?.dataset.idContrato || null;
    window.ID_RESERVACION = contratoApp?.dataset.idReservacion || null;
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Hora por defecto en el input de entrega
    const inputHora = window.$('#nuevaHoraEntrega');
    if (inputHora && !inputHora.value) {
        const ahora = new Date();
        inputHora.value = `${String(ahora.getHours()).padStart(2, '0')}:${String(ahora.getMinutes()).padStart(2, '0')}`;
        if (window.ID_RESERVACION) setTimeout(() => window.actualizarFechasYRecalcular(), 200);
    }

    // ── Modal de vehículos ─────────────────────────────────────────────

    window.abrirModalVehiculos = async () => {
        const modal = window.$('#modalVehiculos');

        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show-modal', 'active');
            document.body.style.overflow = 'hidden';
        }

        // Cargamos 'todos' los vehículos sin importar la categoría inicial
        await window.cargarVehiculosCategoriaModal('todos');
    };

    window.cerrarModalVehiculos = () => {
        const modal = document.getElementById('modalVehiculos');
        if (!modal) return;
        modal.classList.remove('show', 'show-modal', 'active');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    };

    document.getElementById('cerrarModalVehiculos')?.addEventListener('click', window.cerrarModalVehiculos);
    document.getElementById('cerrarModalVehiculos2')?.addEventListener('click', window.cerrarModalVehiculos);

    // ── Cargar vehículos en el modal ─────────────────────────────────────

    window.cargarVehiculosCategoriaModal = async (idCategoria) => {
        if (!idCategoria || !window.ID_RESERVACION) {
            console.warn('⚠️ Faltan datos para cargar vehículos:', { idCategoria, idReservacion: window.ID_RESERVACION });
            return;
        }

        try {
            const resp = await fetch(`/admin/contrato/vehiculos-por-categoria/${idCategoria}/${window.ID_RESERVACION}`);
            const data = await resp.json();

            if (data.success) {
                window.listaVehiculosOriginal = data.data;
                window.renderVehiculosEnModal(data.data);
            } else {
                console.error('❌ Error al cargar vehículos:', data.error);
                const cont = window.$('#listaVehiculosTabla');
                if (cont) {
                    cont.innerHTML = `<tr><td colspan="13" style="padding:20px;text-align:center;color:#dc2626;font-weight:bold;">Error al cargar vehículos: ${data.error || 'Error desconocido'}</td></tr>`;
                }
            }
        } catch (err) {
            console.error('❌ Error de conexión al cargar vehículos:', err);
            const cont = window.$('#listaVehiculosTabla');
            if (cont) {
                cont.innerHTML = `<tr><td colspan="13" style="padding:20px;text-align:center;color:#dc2626;font-weight:bold;">Error de conexión al cargar el inventario.</td></tr>`;
            }
        }
    };

    // ── Renderizar vehículos en el modal ──────────────────────────────

    window.renderVehiculosEnModal = (lista) => {
        const cont = window.$('#listaVehiculosTabla');
        if (!cont) return;

        if (!lista?.length) {
            cont.innerHTML = `<tr><td colspan="13" style="padding:20px;text-align:center;color:#555;font-weight:bold;">No hay vehículos disponibles en la categoría reservada.</td></tr>`;
            return;
        }

        cont.innerHTML = lista.map((v, i) => {
            const capacidadTanque = parseFloat(v.capacidad_tanque) || 60;
            const litrosActuales = parseFloat(v.gasolina_actual) || 0;
            const g = Math.round((litrosActuales / capacidadTanque) * 16);
            const fraccion = `${g}/16`;
            const gasLitros = Math.round(litrosActuales);
            const mantKm = v.km_restantes !== null ? `${v.km_restantes} Km` : '—';
            const vigenciaPoliza = v.dias_seguro !== undefined ? `${v.dias_seguro} Días` : (v.fin_vigencia_poliza ?? '—');
            const diasVerif = v.dias_verificacion !== undefined ? `${v.dias_verificacion} Días` : '—';

            let accion = '', rowStyle = '';

            if (v.es_el_actual) {
                rowStyle = 'background-color:#dcfce7;color:#166534;';
                accion = `<b style="font-size:11px;">ACTUAL</b>`;
            } else if (v.bloqueado_por_codigo) {
                rowStyle = 'background-color:#fee2e2;color:#991b1b;opacity:0.8;';
                accion = `<span style="font-size:10px;font-weight:bold;cursor:help;" title="Bloqueado por: ${v.bloqueado_por_codigo}">Ocupado</span>`;
            } else {
                accion = `<button type="button" class="btn primary btn-vehiculo" style="padding:4px 16px;font-size:12px;" data-id="${v.id_vehiculo}" data-gasolina="${fraccion}">Elegir</button>`;
            }

            return `
            <tr style="${rowStyle}"
                        data-id-vehiculo="${v.id_vehiculo}"
                        data-placa="${v.placa || 'Sin Placa'}"
                        data-color="${v.color || '—'}"
                        data-categoria="${v.categoria_nombre || v.categoria || '—'}"
                        data-gas-original="${g}"
                        data-km-original="${v.kilometraje ?? 0}"
                        data-capacidad-tanque="${capacidadTanque}">
                <td>${i + 1}</td>
                <td><b>${v.placa || 'Sin Placa'}</b></td>
                <td>${v.categoria_nombre || v.categoria || '—'}</td>
                <td>${v.modelo || '—'}</td>
                <td>${v.transmision || '—'}</td>
                <td>${v.color || '—'}</td>
                <td class="celda-editable" data-campo="gasolina" data-tipo="gas" title="Doble clic o ✏️ para editar">
                    <span class="celda-valor">${fraccion}</span>
                    <button type="button" class="btn-edit-inline" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:13px;margin-left:4px;">✏️</button>
                </td>
                <td class="celda-litros">${gasLitros}</td>
                <td class="celda-editable" data-campo="kilometraje" data-tipo="km" title="Doble clic o ✏️ para editar">
                    <span class="celda-valor">${v.kilometraje?.toLocaleString() || '—'}</span>
                    <button type="button" class="btn-edit-inline" style="background:none;border:none;color:#2563eb;cursor:pointer;font-size:13px;margin-left:4px;">✏️</button>
                </td>
                <td>${diasVerif}</td>
                <td>${mantKm}</td>
                <td>${vigenciaPoliza}</td>
                <td>${accion}</td>
            </tr>`;
        }).join('');

        // Asignar eventos a los botones "Elegir" - ASIGNACIÓN DIRECTA (sin confirmación)
        window.$$('.btn-vehiculo').forEach(btn => {
            btn.onclick = async (e) => {
                e.stopPropagation();
                await window.seleccionarVehiculoDirecto(btn.dataset.id, btn);
            };
        });

        // Evento para seleccionar vehículo al hacer clic en la fila - CON MODAL DE CONFIRMACIÓN
        cont.querySelectorAll('tr').forEach(fila => {
            // Si la fila ya tiene estilo de "ACTUAL", no hacer nada
            if (fila.style.backgroundColor === '#dcfce7') return;
            // Si la fila está bloqueada, no hacer nada
            if (fila.style.backgroundColor === '#fee2e2' || fila.style.opacity === '0.8') return;

            fila.addEventListener('click', function(e) {
                // Si se hizo clic en un botón o en el lápiz, no hacer nada (ya tienen sus eventos)
                if (e.target.closest('.btn-vehiculo')) return;
                if (e.target.closest('.btn-edit-inline')) return;

                // Obtener el ID del vehículo desde el dataset de la fila
                const idVehiculo = this.dataset.idVehiculo;
                if (idVehiculo) {
                    // Abrir modal de confirmación al hacer clic en la fila
                    window.seleccionarVehiculoConConfirmacion(idVehiculo, this);
                }
            });
        });
    };

    // ── Selección de vehículo DIRECTO (sin modal de confirmación) ─────────────────
    // Esto se usa para el botón "Elegir"

    window.seleccionarVehiculoDirecto = async (idVehiculo, btnEl) => {
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '⌛...';
        }

        try {
            const resp = await fetch('/admin/contrato/asignar-vehiculo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    id_vehiculo: idVehiculo
                }),
            });
            const data = await resp.json();

            if (data.success) {
                window.alertify?.success('Vehículo asignado.');
                const cIni = document.getElementById('contratoInicial');
                if (cIni) cIni.dataset.idVehiculo = String(idVehiculo);

                // Cerrar modal y actualizar
                window.cerrarModalVehiculos();
                setTimeout(() => window.cargarResumenBasico?.(), 150);
            } else {
                window.alertify?.error(data.error || 'Error al asignar.');
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = 'Elegir';
                }
            }
        } catch (err) {
            console.error(err);
            window.alertify?.error('Error de conexión.');
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = 'Elegir';
            }
        }
    };

    // ── Selección de vehículo CON MODAL DE CONFIRMACIÓN ─────────────────
    // Esto se usa para el clic en la fila

    // Variable para almacenar el vehículo seleccionado temporalmente
    let vehiculoSeleccionadoTemp = null;

    // Función para abrir el modal de confirmación
    function abrirConfirmacionVehiculo(idVehiculo, fila) {
        // Obtener datos de la fila
        const placas = fila.dataset.placa || '—';
        const modelo = fila.querySelector('td:nth-child(4)')?.textContent?.trim() || '—';
        const categoria = fila.dataset.categoria || '—';
        const color = fila.dataset.color || '—';
        const gasActual = parseInt(fila.dataset.gasOriginal) || 16;
        const kmActual = parseInt(fila.dataset.kmOriginal) || 0;

        // Guardar temporalmente
        vehiculoSeleccionadoTemp = {
            id: idVehiculo,
            placas: placas,
            modelo: modelo,
            categoria: categoria,
            color: color,
            gasActual: gasActual,
            kmActual: kmActual,
            fila: fila
        };

        // Llenar datos en el modal
        document.getElementById('confPlacasVehiculo').textContent = placas;
        document.getElementById('confModeloVehiculo').textContent = modelo;
        document.getElementById('confCategoriaVehiculo').textContent = categoria;
        document.getElementById('confColorVehiculo').textContent = color;

        // Select de gasolina - seleccionar el valor actual
        const gasSelect = document.getElementById('confGasolinaSelect');
        gasSelect.value = gasActual;
        actualizarLitrosTexto(gasActual);

        // Input de kilometraje
        document.getElementById('confKilometrajeInput').value = kmActual;

        // Abrir modal
        const modal = document.getElementById('modalConfirmarVehiculo');
        modal.style.display = 'flex';
        modal.classList.add('show-modal');
        document.body.style.overflow = 'hidden';
    }

    // Función para actualizar el texto de litros
    function actualizarLitrosTexto(nivel) {
        const litros = Math.round((nivel / 16) * 60);
        const el = document.getElementById('confLitrosTexto');
        if (el) el.textContent = `~${litros} L`;
    }

    // Función para cerrar el modal de confirmación
    function cerrarConfirmacionVehiculo() {
        const modal = document.getElementById('modalConfirmarVehiculo');
        if (!modal) return;
        modal.classList.remove('show-modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        vehiculoSeleccionadoTemp = null;
    }

    // Función para confirmar la selección del vehículo (desde el modal de confirmación)
    async function confirmarSeleccionVehiculo() {
        if (!vehiculoSeleccionadoTemp) {
            window.alertify?.error('No hay vehículo seleccionado.');
            return;
        }

        const btnConfirmar = document.getElementById('confirmarSeleccionVehiculo');
        const textoOriginal = btnConfirmar?.innerHTML || 'Confirmar';
        if (btnConfirmar) {
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }

        try {
            const idVehiculo = vehiculoSeleccionadoTemp.id;
            const nuevoGas = parseInt(document.getElementById('confGasolinaSelect')?.value) || 16;
            const nuevoKm = parseInt(document.getElementById('confKilometrajeInput')?.value) || 0;

            // Actualizar gasolina
            await fetch('/admin/vehiculo/actualizar-inventario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_vehiculo: idVehiculo,
                    campo: 'gasolina',
                    valor: nuevoGas
                })
            });

            // Actualizar kilometraje si cambió
            if (nuevoKm !== vehiculoSeleccionadoTemp.kmActual) {
                await fetch('/admin/vehiculo/actualizar-inventario', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        id_vehiculo: idVehiculo,
                        campo: 'kilometraje',
                        valor: nuevoKm
                    })
                });
            }

            // Asignar vehículo a la reservación
            const asignarResp = await fetch('/admin/contrato/asignar-vehiculo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_reservacion: window.ID_RESERVACION,
                    id_vehiculo: idVehiculo
                })
            });

            const data = await asignarResp.json();

            if (data.success) {
                // Actualizar UI
                const cIni = document.getElementById('contratoInicial');
                if (cIni) cIni.dataset.idVehiculo = String(idVehiculo);

                // Actualizar la fila en el inventario
                const fila = vehiculoSeleccionadoTemp.fila;
                if (fila) {
                    document.querySelectorAll('#listaVehiculosTabla tr').forEach(tr => {
                        tr.style.backgroundColor = '';
                        tr.style.color = '';
                        const accionCelda = tr.querySelector('td:last-child');
                        if (accionCelda) {
                            const btnElegir = accionCelda.querySelector('.btn-vehiculo');
                            if (btnElegir) {
                                accionCelda.innerHTML = '<button type="button" class="btn primary btn-vehiculo" style="padding:4px 16px;font-size:12px;" data-id="' + idVehiculo + '">Elegir</button>';
                            }
                        }
                        tr.classList.remove('seleccionado');
                    });

                    fila.style.backgroundColor = '#dcfce7';
                    fila.style.color = '#166534';
                    const accionCelda = fila.querySelector('td:last-child');
                    if (accionCelda) {
                        accionCelda.innerHTML = '<b style="font-size:11px;">ACTUAL</b>';
                    }
                    fila.classList.add('seleccionado');
                }

                // Actualizar gasolina en paso 1
                const inputGas = document.getElementById('gasNivelActual');
                if (inputGas) inputGas.value = `${nuevoGas}/16`;

                // Cerrar ambos modales
                cerrarConfirmacionVehiculo();
                window.cerrarModalVehiculos();

                // Actualizar resumen
                setTimeout(() => {
                    window.cargarResumenBasico?.();
                }, 300);

                window.alertify?.success('Vehículo asignado correctamente.');
            } else {
                window.alertify?.error(data.error || 'Error al asignar el vehículo.');
                cerrarConfirmacionVehiculo();
            }
        } catch (err) {
            console.error('Error al confirmar vehículo:', err);
            window.alertify?.error('Error de conexión al guardar los datos.');
            cerrarConfirmacionVehiculo();
        } finally {
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = textoOriginal;
            }
        }
    }

    // Función para seleccionar vehículo con confirmación (clic en fila)
    window.seleccionarVehiculoConConfirmacion = function(idVehiculo, fila) {
        if (fila) {
            if (fila.style.backgroundColor === '#fee2e2' || fila.style.opacity === '0.8') {
                window.alertify?.warning('Este vehículo está ocupado por otra reservación.');
                return;
            }
            abrirConfirmacionVehiculo(idVehiculo, fila);
        } else {
            window.alertify?.error('No se encontró la información del vehículo.');
        }
    };

    // Exponer funciones globalmente
    window.abrirConfirmacionVehiculo = abrirConfirmacionVehiculo;
    window.cerrarConfirmacionVehiculo = cerrarConfirmacionVehiculo;
    window.confirmarSeleccionVehiculo = confirmarSeleccionVehiculo;

    // ──────────────────────────────────────────────────────────────────────
    // Eventos del modal de confirmación
    // ──────────────────────────────────────────────────────────────────────

    document.getElementById('cerrarConfirmarVehiculo')?.addEventListener('click', cerrarConfirmacionVehiculo);
    document.getElementById('cancelarConfirmarVehiculo')?.addEventListener('click', cerrarConfirmacionVehiculo);
    document.getElementById('confirmarSeleccionVehiculo')?.addEventListener('click', confirmarSeleccionVehiculo);

    document.getElementById('modalConfirmarVehiculo')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarConfirmacionVehiculo();
        }
    });

    document.getElementById('confGasolinaSelect')?.addEventListener('change', function() {
        actualizarLitrosTexto(parseInt(this.value) || 0);
    });

    // ── Navegación de pasos ────────────────────────────────────────────

    window.showStep = (n) => {
        window.$$('.step').forEach(el => el.classList.toggle('active', Number(el.dataset.step) === n));
        window.actualizarStepper?.(n);
        if (n === 6) window.cargarPaso6?.();
    };

    // ── Resumen básico ─────────────────────────────────────────────────

    const setTxt = (sel, val) => window.$$(sel).forEach(el => el.textContent = val ?? '—');

    const actualizarCalendario = (selector, fechaSql) => {
        if (!fechaSql) return;
        const [anio, mes, dia] = fechaSql.split('-');
        const container = window.$(selector);
        if (!container) return;
        container.querySelector('.dia')?.textContent !== undefined && (container.querySelector('.dia').textContent = dia);
        container.querySelector('.anio')?.textContent !== undefined && (container.querySelector('.anio').textContent = anio);
        const elMes = container.querySelector('.mes');
        if (elMes) elMes.textContent = new Date(`${fechaSql}T00:00:00`).toLocaleString('es-MX', { month: 'short' }).toUpperCase().replace('.', '');
    };

    const pintarLista = (id, list) => {
        const el = window.$(id);
        if (!el) return;
        if (!Array.isArray(list) || list.length === 0) { el.innerHTML = `<li class="empty">—</li>`; return; }
        el.innerHTML = list.map(i => {
            const valor = i.total ?? i.precio ?? i.monto ?? i.precio_total ?? 0;
            return `<li style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px;">
                <span>${i.nombre || i.concepto || 'Adicional'}</span>
                <b>${window.money(valor)}</b>
            </li>`;
        }).join('');
    };

    window.cargarResumenBasico = async () => {
        if (!window.ID_RESERVACION) return;

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/resumen?t=${Date.now()}`);
            if (!resp.ok) return;
            const { success, data: r } = await resp.json();
            if (!success) return;

            setTxt('#detCodigo', r.codigo);

            if (r.cliente) {
                setTxt('#detCliente', r.cliente.nombre?.toUpperCase());
                setTxt('#detTelefono', window.formatPhone(r.cliente.telefono));
                setTxt('#detEmail', r.cliente.email);
            }

            const cIni = document.getElementById('contratoInicial');

            if (r.vehiculo) {
                if (cIni) {
                    cIni.dataset.idVehiculo = r.vehiculo.id_vehiculo || '';
                    if (r.vehiculo.id_categoria) {
                        cIni.dataset.idCategoria = r.vehiculo.id_categoria;
                    }
                }

                setTxt('#detModelo', r.vehiculo.modelo);
                setTxt('#detMarca', r.vehiculo.marca);
                setTxt('#detCategoria', r.vehiculo.categoria);
                setTxt('#detTransmision', r.vehiculo.transmision);
                setTxt('#detKm', r.vehiculo.km ? `${r.vehiculo.km.toLocaleString()} km` : '0 km');
                setTxt('#detPasajeros', r.vehiculo.asientos);
                setTxt('#detPuertas', r.vehiculo.puertas);
                setTxt('#step1Puertas', r.vehiculo.puertas);
                setTxt('#step1Pasajeros', r.vehiculo.asientos);
                setTxt('#step1Transmision', r.vehiculo.transmision);

                const nombre = r.vehiculo.nombre_publico || `${r.vehiculo.marca} ${r.vehiculo.modelo}`;
                setTxt('#resumenVehCompacto', nombre);
                setTxt('#detNombreVehiculoStep1', nombre);
                setTxt('#resumenCategoriaCompacto', r.vehiculo.categoria);

                const codigoCat = r.vehiculo.codigo_categoria || 'C';
                setTxt('#detCategoriaCodigoStep1', codigoCat);
                setTxt('#detCategoriaNombreStep1', (r.vehiculo.categoria || '').toUpperCase());

                const modalCat = document.getElementById('contenedorCategoriasJS');
                if (modalCat) {
                    modalCat.querySelectorAll(".card-categoria").forEach(c => {
                        const active = (c.dataset.idCategoria == (r.vehiculo.id_categoria || cIni?.dataset.idCategoria));
                        c.classList.toggle("activa", active);
                        const badge = c.querySelector(".cat-badge-actual");
                        if (badge && !active) badge.remove();
                        if (active && !badge) {
                            const newBadge = document.createElement("span");
                            newBadge.className = "cat-badge-actual";
                            newBadge.textContent = "Actual";
                            c.appendChild(newBadge);
                        }
                    });
                }

                const imgSrc = r.vehiculo.imagen_render?.includes('iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAQAAADwXcor')
                    ? getLocalImg(codigoCat)
                    : (r.vehiculo.imagen_render || getLocalImg(codigoCat));

                window.$$('.resumenImgVeh, #resumenImgVeh, #mainImgVeh').forEach(el => {
                    if (el && el.src !== imgSrc) {
                        el.src = imgSrc;
                        el.style.display = 'block';
                        el.style.objectFit = 'contain';
                    }
                });
            } else {
                if (cIni) cIni.dataset.idVehiculo = '';
                ['#detModelo', '#detMarca', '#detCategoria', '#detTransmision', '#detKm',
                    '#resumenVehCompacto', '#detPasajeros', '#detPuertas'].forEach(id => setTxt(id, '—'));
                setTxt('#detModelo', 'Sin asignar');
                setTxt('#detNombreVehiculoStep1', 'Vehículo sin asignar');
                window.$$('.resumenImgVeh, #resumenImgVeh, #mainImgVeh').forEach(el => { if (el) el.src = IMG_FALLBACK; });
            }

            if (r.fechas) {
                const hoyStr = new Date().toISOString().split('T')[0];
                setTxt('#detFechaSalida', r.fechas.inicio);
                setTxt('#detFechaEntrega', r.fechas.fin);
                if (r.fechas.inicio !== hoyStr) setTxt('#detHoraSalida', r.fechas.hora_inicio);
                if (r.fechas.fin !== hoyStr) setTxt('#detHoraEntrega', r.fechas.hora_fin);

                setTxt('#detDiasRenta', r.fechas.dias);
                setTxt('#resumenDiasCompacto', `Días de renta: ${r.fechas.dias}`);
                setTxt('#resumenFechasCompacto', `${r.fechas.inicio} / ${r.fechas.fin}`);
                setTxt('.bloque.entrega .hora', r.fechas.hora_inicio);
                setTxt('.bloque.devolucion .hora', r.fechas.hora_fin);
                setTxt('#diasBadge', `${r.fechas.dias} días`);

                actualizarCalendario('.fecha-entrega-display', r.fechas.inicio);
                actualizarCalendario('.fecha-devolucion-display', r.fechas.fin);
            }

            pintarLista('#r_seguros_lista', r.seguros?.lista);
            setTxt('#r_seguros_total', window.money(r.seguros?.total || 0));

            const todosAdicionales = [...(r.servicios || []), ...(r.cargos || [])];
            const totalAdicionales = (r.totales?.servicios_total || 0) + (r.totales?.cargos_total || 0);
            pintarLista('#r_servicios_lista', todosAdicionales);
            setTxt('#r_servicios_total', window.money(totalAdicionales));

            _manejarReintentosCargos(r);

            if (r.totales) {
                const granTotal = parseFloat(r.totales.total || 0);
                const tarifa = parseFloat(r.totales.tarifa_modificada) > 0
                    ? r.totales.tarifa_modificada
                    : r.totales.tarifa_base;

                setTxt('#resumenTotalBarra', window.money(granTotal));
                setTxt('#resumenTotalUsd', `$${(granTotal / 18.5).toFixed(2)} USD`);
                setTxt('#resumenTotalCompacto', window.money(granTotal));
                setTxt('#btnTotalTextContrato', window.money(granTotal));
                setTxt('#btnTotalUsdContrato', `$${(granTotal / 18.5).toFixed(2)} USD`);
                setTxt('#r_total_final', window.money(granTotal));
                setTxt('#r_subtotal', window.money(r.totales.subtotal));
                setTxt('#r_iva', window.money(r.totales.iva));
                setTxt('#r_base_precio', window.money(tarifa));
                setTxt('#r_cortesia', r.totales.horas_cortesia ?? 0);
                setTxt('#totalReserva', window.money(tarifa * parseInt(r.fechas?.dias || 1)));
            }

            if (r.pagos) {
                setTxt('#detPagos', window.money(r.pagos.realizados));
                setTxt('#detSaldo', window.money(r.pagos.saldo));
            }

        } catch (e) {
            console.error('❌ Error cargarResumenBasico:', e);
        }
    };

    // Lógica de reintento cuando hay switches activos pero cargos aún vacíos
    const MAX_RETRIES = 5;
    let _retryContador = 0;
    let _retryTimer = null;

    function _manejarReintentosCargos(r) {
        const dropActivo = document.getElementById('switchDropoffCheckbox')?.checked;
        const gasActivo = document.getElementById('switchGasolinaCheckbox')?.checked;
        const delivActivo = document.getElementById('deliveryToggle')?.checked;
        const haySwitch = dropActivo || gasActivo || delivActivo;

        if (haySwitch && (!r.cargos || r.cargos.length === 0)) {
            if (_retryContador < MAX_RETRIES) {
                _retryContador++;
                if (!_retryTimer) {
                    _retryTimer = setTimeout(async () => {
                        _retryTimer = null;
                        await window.cargarResumenBasico();
                    }, 500);
                }
            } else {
                _retryContador = 0;
            }
        } else {
            _retryContador = 0;
            if (_retryTimer) { clearTimeout(_retryTimer); _retryTimer = null; }
        }
    }

    // ── Menú desplegable del resumen (barra verde) ─────────────────────

    const btnToggle = document.getElementById('btnToggleDetalle');
    const detalleCont = document.getElementById('resumenDetalleContainer');
    const iconoFlecha = document.getElementById('iconoFlechaResumen');

    if (btnToggle && detalleCont) {
        const cerrarMenu = () => {
            detalleCont.classList.remove('show');
            iconoFlecha?.classList.remove('rotada');
            btnToggle.style.borderBottomLeftRadius = '12px';
            btnToggle.style.borderBottomRightRadius = '12px';
        };

        btnToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const abierto = detalleCont.classList.toggle('show');
            iconoFlecha?.classList.toggle('rotada', abierto);
            btnToggle.style.borderBottomLeftRadius = abierto ? '0' : '12px';
            btnToggle.style.borderBottomRightRadius = abierto ? '0' : '12px';
        });

        document.addEventListener('click', (e) => {
            if (!btnToggle.contains(e.target) && !detalleCont.contains(e.target)) cerrarMenu();
        });
    }

    // Sincronizar barra verde con el nodo de total compacto via MutationObserver
    const totalCompactoNode = document.getElementById('resumenTotalCompacto');
    const barraVerdeNode = document.getElementById('resumenTotalBarra');
    const usdNode = document.getElementById('resumenTotalUsd');

    if (totalCompactoNode && barraVerdeNode) {
        new MutationObserver(() => {
            barraVerdeNode.innerText = totalCompactoNode.innerText;
            const val = parseFloat(totalCompactoNode.innerText.replace(/[^0-9.]/g, ''));
            if (!isNaN(val) && usdNode) usdNode.innerText = '$' + (val / 18.5).toFixed(2) + ' USD';
        }).observe(totalCompactoNode, { childList: true, characterData: true, subtree: true });
    }

    // ── Edición de fechas y recálculo ──────────────────────────────────

    window.actualizarFechasYRecalcular = async (tarifaManual = null, cortesiaManual = null) => {
        const cIni = document.getElementById('contratoInicial');
        const inputE = document.getElementById('inputOcultoEntrega');
        const inputD = document.getElementById('inputOcultoDevolucion');

        if (!inputE?.value || !inputD?.value) return;

        const [fechaInicio, horaInicio] = inputE.value.split('T');
        const [fechaFin, horaFin] = inputD.value.split('T');
        const idCategoria = cIni?.dataset.idCategoria;

        if (!fechaInicio || !fechaFin || !idCategoria) return;

        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/recalcular-total`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({ fecha_inicio: fechaInicio, hora_inicio: horaInicio, fecha_fin: fechaFin, hora_fin: horaFin, id_categoria: idCategoria, tarifa_manual: tarifaManual, horas_cortesia: cortesiaManual }),
            });

            const data = await resp.json();

            if (data.success) {
                await window.cargarResumenBasico?.();

                const set = (id, val) => { const el = window.$(id); if (el) el.textContent = val; };
                set('#detFechaSalida', data.fecha_inicio);
                set('#detHoraSalida', data.hora_inicio);
                set('#detFechaEntrega', data.fecha_fin);
                set('#detHoraEntrega', data.hora_fin);
                set('#detDiasRenta', data.dias);
                set('#diasBadge', `${data.dias} días`);
                set('#resumenDiasCompacto', `Días de renta: ${data.dias}`);
                set('#resumenFechasCompacto', `${data.fecha_inicio} / ${data.fecha_fin}`);

                if (cIni) {
                    cIni.dataset.inicio = fechaInicio;
                    cIni.dataset.fin = fechaFin;
                    cIni.dataset.horaRetiro = horaInicio;
                    cIni.dataset.horaEntrega = horaFin;
                }
            }
        } catch (err) {
            console.error('❌ Error recalcular:', err);
            window.cargarResumenBasico?.();
        }
    };

    // ── Editar tarifa (inline) ─────────────────────────────────────────

    window.$('#btnEditarTarifa')?.addEventListener('click', () => {
        const contenedor = window.$('#r_base_precio');
        if (!contenedor) return;

        const precioActual = contenedor.textContent.replace(/[^\d.-]/g, '');
        const input = Object.assign(document.createElement('input'), {
            type: 'number', step: '0.01', value: parseFloat(precioActual) || 0,
        });
        Object.assign(input.style, { border: '1px solid #2563eb', background: '#fff', width: '100px', fontWeight: 'bold', padding: '2px 4px', borderRadius: '4px' });

        contenedor.innerHTML = '';
        contenedor.appendChild(input);
        input.focus();
        input.select();

        input.addEventListener('blur', async () => {
            const nuevoPrecio = parseFloat(input.value);
            if (!isNaN(nuevoPrecio) && nuevoPrecio >= 0) {
                contenedor.textContent = 'Guardando...';
                try {
                    const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/editar-tarifa`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                        body: JSON.stringify({ tarifa_modificada: nuevoPrecio }),
                    });
                    const data = await resp.json();
                    if (data.ok) {
                        await window.cargarResumenBasico?.();
                        if (window.$('#baseAmt')) window.cargarPaso6?.();
                        window.alertify?.success('Tarifa actualizada');
                    } else throw new Error('Error backend');
                } catch {
                    contenedor.textContent = window.money(precioActual);
                    window.alertify?.error('Error al actualizar');
                }
            } else {
                contenedor.textContent = window.money(precioActual);
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') input.blur();
            if (e.key === 'Escape') contenedor.textContent = window.money(precioActual);
        });
    });

    // ── Editar cortesía (inline) ───────────────────────────────────────

    window.$('#btnEditarCortesia')?.addEventListener('click', () => {
        const contenedor = window.$('#r_cortesia');
        if (!contenedor) return;

        const valorActual = contenedor.textContent.trim();
        const input = Object.assign(document.createElement('input'), {
            type: 'number', min: 1, max: 3,
            value: Math.min(Math.max(parseInt(valorActual) || 1, 1), 3),
        });
        Object.assign(input.style, { width: '55px', border: '1px solid #2563eb', textAlign: 'center', fontWeight: 'bold', borderRadius: '4px' });

        contenedor.innerHTML = '';
        contenedor.appendChild(input);
        input.focus();
        input.select();

        input.addEventListener('blur', async () => {
            let nuevoValor = parseInt(input.value);
            if (nuevoValor > 3) { window.alertify?.error('El límite máximo de cortesía es de 3 horas.'); nuevoValor = 3; }
            if (isNaN(nuevoValor) || nuevoValor < 1) nuevoValor = 1;

            contenedor.textContent = '...';
            try {
                await window.actualizarFechasYRecalcular(null, nuevoValor);
                contenedor.textContent = nuevoValor;
            } catch {
                contenedor.textContent = valorActual;
                window.alertify?.error('Error al guardar la cortesía.');
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') input.blur();
            if (e.key === 'Escape') contenedor.textContent = valorActual;
        });
    });

    // ── Cambio de categoría ────────────────────────────────────────────

    window.$('#selectCategoria')?.addEventListener('change', async (e) => {
        ['#detModelo', '#detMarca', '#detCategoria', '#detTransmision', '#detKm',
            '#resumenVehCompacto', '#detPasajeros', '#detPuertas'].forEach(id => setTxt(id, 'Actualizando...'));
        try {
            const resp = await fetch(`/admin/contrato/${window.ID_RESERVACION}/actualizar-categoria`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({ id_categoria: e.target.value }),
            });
            if (resp.ok) await window.cargarResumenBasico();
        } catch (err) { console.error(err); }
    });

    // ── Sidebar detallada (toggle) ─────────────────────────────────────

    window.$('#btnVerDetalle')?.addEventListener('click', () => {
        window.$('#resumenCompacto') && (window.$('#resumenCompacto').style.display = 'none');
        window.$('#resumenDetalle') && (window.$('#resumenDetalle').style.display = 'block');
        window.ContratoStore.set('sidebarDetallada', true);
    });

    window.$('#btnOcultarDetalle')?.addEventListener('click', () => {
        window.$('#resumenDetalle') && (window.$('#resumenDetalle').style.display = 'none');
        window.$('#resumenCompacto') && (window.$('#resumenCompacto').style.display = 'block');
        window.ContratoStore.set('sidebarDetallada', false);
    });

    if (window.ContratoStore.get('sidebarDetallada', false)) {
        window.$('#resumenCompacto') && (window.$('#resumenCompacto').style.display = 'none');
        window.$('#resumenDetalle') && (window.$('#resumenDetalle').style.display = 'block');
    }

    // Carga inicial
    if (window.ID_RESERVACION) window.cargarResumenBasico();

    // ── Reloj en vivo ──────────────────────────────────────────────────

    let intervaloReloj = null;

    const hora12h = (date) => {
        const h = date.getHours(), m = date.getMinutes();
        return `${String(h % 12 || 12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
    };

    const actualizarHorasEnVivo = () => {
        const hoy = new Date();

        const sincronizar = (inputId, txtId, resumenId) => {
            const input = document.getElementById(inputId);
            if (!input?.value) return;
            if (new Date(input.value).toDateString() !== hoy.toDateString()) return;
            const hora = hora12h(hoy);
            const elTxt = document.getElementById(txtId);
            const elResumen = document.getElementById(resumenId);
            if (elTxt) elTxt.textContent = hora;
            if (elResumen) elResumen.textContent = hora;
        };

        sincronizar('inputOcultoEntrega', 'txtHoraEntrega', 'detHoraSalida');
        sincronizar('inputOcultoDevolucion', 'txtHoraDevolucion', 'detHoraEntrega');
    };

    const iniciarReloj = () => { if (intervaloReloj) clearInterval(intervaloReloj); actualizarHorasEnVivo(); intervaloReloj = setInterval(actualizarHorasEnVivo, 30000); };
    const detenerReloj = () => { clearInterval(intervaloReloj); intervaloReloj = null; };

    iniciarReloj();
    window.addEventListener('beforeunload', detenerReloj);
    document.getElementById('go4')?.addEventListener('click', detenerReloj);

    // ── Stepper (navegación por clicks) ───────────────────────────────

    document.querySelectorAll('.stepper-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetStep = parseInt(item.getAttribute('data-step-indicator'));
            const enPasosIniciales = !!document.querySelector('.step[data-step="1"]');
            const idRes = window.ID_RESERVACION || document.getElementById('contratoInicial')?.dataset.idReservacion;

            if (enPasosIniciales) {
                if (targetStep <= 3) {
                    window.showStep(targetStep);
                } else {
                    if (!idRes) return;
                    if (!document.getElementById('contratoInicial')?.dataset.idVehiculo) {
                        return window.alertify?.warning('⚠️ Selecciona un vehículo primero.');
                    }
                    localStorage.setItem(`contratoPasoActual_${idRes}`, targetStep.toString());
                    window.location.href = `/admin/contrato2/${idRes}`;
                }
            } else {
                if (targetStep <= 3) {
                    localStorage.setItem(`contratoPasoActual_${idRes}`, targetStep.toString());
                    window.location.href = `/admin/contrato/${idRes}`;
                } else {
                    window.showStep(targetStep);
                }
            }
        });
    });
});
