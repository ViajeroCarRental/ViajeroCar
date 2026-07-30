// ============================================
// CARGA INICIAL
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    cargarReservaciones();
    cargarContratos();
    cargarFacturadas();
    cargarCanceladas();
    inicializarModal();
    inicializarCancelacion();
});

// ============================================
// PESTAÑAS
// ============================================
function mostrarTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => { b.style.background = '#ccc'; b.style.color = '#333'; });
    document.getElementById('tabla-' + tab).style.display = 'block';
    const btn = document.getElementById('tab-' + tab);
    if (btn) { btn.style.background = '#ff0000'; btn.style.color = '#fff'; }
}

// ============================================
// RESERVACIONES (solo no facturadas)
// ============================================
function cargarReservaciones() {
    fetch('/api/facturar/reservaciones')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tbody-reservaciones');
            tbody.innerHTML = '';
            const noFac = data.filter(r => !r.facturada);
            if (noFac.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:2rem; color:#888;">No hay reservaciones sin facturar.</td></tr>';
                return;
            }
            noFac.forEach(r => {
                const nombre = `${r.nombre_cliente} ${r.apellidos_cliente}`;
                tbody.innerHTML += `
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:.75rem;">${r.codigo}</td>
                        <td style="padding:.75rem;">${nombre}</td>
                        <td style="padding:.75rem;">${r.email_cliente}</td>
                        <td style="padding:.75rem;">$${parseFloat(r.total).toFixed(2)}</td>
                        <td style="padding:.75rem;">
                            <button onclick="abrirModal('${r.codigo}', '${nombre.replace(/'/g, "\\'")}', '${r.email_cliente}', ${r.total})"
                                style="background:#ff0000; color:#fff; padding:.4rem .8rem; border:none; border-radius:6px; cursor:pointer;">
                                <i class="fas fa-file-invoice"></i> Facturar
                            </button>
                        </td>
                    </tr>`;
            });
        })
        .catch(err => console.error('Error reservaciones:', err));
}

// ============================================
// CONTRATOS (solo no facturados)
// ============================================
function cargarContratos() {
    Promise.all([
        fetch('/api/contratos-abiertos').then(r => r.json()),
        fetch('/api/facturar/folios-facturados').then(r => r.json())
    ])
        .then(([contratos, facturados]) => {
            const tbody = document.getElementById('tbody-contratos');
            tbody.innerHTML = '';
            const noFac = contratos.filter(c => !facturados.includes(c.codigo));
            if (noFac.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:2rem; color:#888;">No hay contratos sin facturar.</td></tr>';
                return;
            }
            noFac.forEach(c => {
                const nombre = `${c.nombre_cliente || ''} ${c.apellidos_cliente || ''}`;
                const correo = c.email_cliente || '';
                const total = c.total || 0;
                tbody.innerHTML += `
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:.75rem;">${c.codigo}</td>
                    <td style="padding:.75rem;">${c.numero_contrato || ''}</td>
                    <td style="padding:.75rem;">${nombre}</td>
                    <td style="padding:.75rem;">${correo}</td>
                    <td style="padding:.75rem;">
                        <button onclick="abrirModal('${c.codigo}', '${nombre.replace(/'/g, "\\'")}', '${correo}', ${total})"
                            style="background:#16213e; color:#fff; padding:.4rem .8rem; border:none; border-radius:6px; cursor:pointer;">
                            <i class="fas fa-file-invoice"></i> Facturar
                        </button>
                    </td>
                </tr>`;
            });
        })
        .catch(err => console.error('Error contratos:', err));
}

// ============================================
// FACTURADAS
// ============================================
function cargarFacturadas() {
    fetch('/api/facturar/facturadas')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tbody-facturadas');
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:2rem; color:#888;">No hay facturas timbradas.</td></tr>';
                return;
            }
            data.forEach(f => {
                const fecha = f.fecha_timbrado ? new Date(f.fecha_timbrado).toLocaleDateString('es-MX') : '—';
                tbody.innerHTML += `
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:.75rem;">${f.folio_reservacion}</td>
                        <td style="padding:.75rem;">${f.rfc_receptor || ''}</td>
                        <td style="padding:.75rem;">${f.nombre_receptor || ''}</td>
                        <td style="padding:.75rem;">$${parseFloat(f.total).toFixed(2)}</td>
                        <td style="padding:.75rem;">${fecha}</td>
                        <td style="padding:.75rem; white-space:nowrap;">
                            <a href="/admin/facturas/${f.id}/pdf" style="color:#c0392b; margin-right:.6rem;" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="/admin/facturas/${f.id}/xml" style="color:#16213e; margin-right:.6rem;" title="XML"><i class="fas fa-file-code"></i></a>
                            <button onclick="abrirModalEnviar(${f.id}, '${f.folio_reservacion}')" style="background:#2e7d32; color:#fff; border:none; padding:.3rem .6rem; border-radius:6px; cursor:pointer; margin-right:.4rem;" title="Enviar por correo"><i class="fas fa-envelope"></i></button>
                            <button onclick="abrirModalCancelar(${f.id}, '${f.folio_reservacion}')" style="background:#b71c1c; color:#fff; border:none; padding:.3rem .6rem; border-radius:6px; cursor:pointer;" title="Cancelar"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>`;
            });
        })
        .catch(err => console.error('Error facturadas:', err));
}

// ============================================
// CANCELADAS
// ============================================
function cargarCanceladas() {
    fetch('/api/facturar/canceladas')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tbody-canceladas');
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:2rem; color:#888;">No hay facturas canceladas.</td></tr>';
                return;
            }
            data.forEach(f => {
                const fecha = f.fecha_timbrado ? new Date(f.fecha_timbrado).toLocaleDateString('es-MX') : '—';
                tbody.innerHTML += `
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:.75rem;">${f.folio_reservacion}</td>
                    <td style="padding:.75rem;">${f.rfc_receptor || ''}</td>
                    <td style="padding:.75rem;">${f.nombre_receptor || ''}</td>
                    <td style="padding:.75rem;">$${parseFloat(f.total).toFixed(2)}</td>
                    <td style="padding:.75rem;">${fecha}</td>
                    <td style="padding:.75rem;">
                        <button onclick='verDetalleCancelada(${JSON.stringify(f)})' style="background:#16213e; color:#fff; border:none; padding:.3rem .8rem; border-radius:6px; cursor:pointer;">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </td>
                </tr>`;
            });
        })
        .catch(err => console.error('Error canceladas:', err));
}

// ============================================
// MODAL DETALLE DE CANCELADA
// ============================================
function verDetalleCancelada(f) {
    document.getElementById('d_folio').textContent = f.folio_reservacion || '—';
    document.getElementById('d_uuid').textContent = f.uuid || '—';
    document.getElementById('d_rfc').textContent = f.rfc_receptor || '—';
    document.getElementById('d_nombre').textContent = f.nombre_receptor || '—';
    document.getElementById('d_total').textContent = '$' + parseFloat(f.total || 0).toFixed(2);
    document.getElementById('d_fecha_timbrado').textContent = f.fecha_timbrado ? new Date(f.fecha_timbrado).toLocaleString('es-MX') : '—';
    document.getElementById('d_origen').textContent = f.origen || '—';
    document.getElementById('d_facturapi_id').textContent = f.facturapi_id || '—';

    document.getElementById('modalDetalle').style.display = 'block';
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').style.display = 'none';
}

// ============================================
// MODAL DE FACTURACIÓN
// ============================================
function abrirModal(folio, nombre, correo, total) {
    // El total de la reservación incluye IVA; el valor unitario es sin IVA
    const valorSinIva = (parseFloat(total) / 1.16).toFixed(2);

    document.getElementById('f_folio').value = folio;
    document.getElementById('f_valor').value = valorSinIva;
    document.getElementById('f_descripcion').value = 'Renta de vehiculo - Folio ' + folio;
    document.getElementById('f_nombre').value = nombre;
    document.getElementById('f_correo').value = correo;
    document.getElementById('f_cantidad').value = 1;

    // Calcular IVA y total visual
    calcularIvaModal();

    document.getElementById('modalFacturar').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalFacturar').style.display = 'none';
}

function calcularIvaModal() {
    const cant = parseFloat(document.getElementById('f_cantidad').value) || 0;
    const vu = parseFloat(document.getElementById('f_valor').value) || 0;
    const subtotal = cant * vu;
    const iva = subtotal * 0.16;
    document.getElementById('f_iva').value = iva.toFixed(2);
    document.getElementById('f_total').value = (subtotal + iva).toFixed(2);
}

// ============================================
// INICIALIZAR MODAL DE FACTURACIÓN (filtro régimen + cálculo IVA + RFC)
// ============================================
function inicializarModal() {
    const regimen = document.getElementById('f_regimen');
    const uso = document.getElementById('f_uso');
    const usoHint = document.getElementById('f_uso_hint');
    const rfc = document.getElementById('f_rfc');
    const rfcHint = document.getElementById('f_rfc_hint');
    const cantidad = document.getElementById('f_cantidad');
    const valor = document.getElementById('f_valor');

    // Cálculo de IVA cuando cambian cantidad o valor
    if (cantidad) cantidad.addEventListener('input', calcularIvaModal);
    if (valor) valor.addEventListener('input', calcularIvaModal);

    // Filtro régimen ↔ uso
    const USOS = {
        S01: 'S01 – Sin efectos fiscales', CP01: 'CP01 – Pagos',
        G01: 'G01 – Adquisición de mercancías', G02: 'G02 – Devoluciones/descuentos', G03: 'G03 – Gastos en general',
        I01: 'I01 – Construcciones', I02: 'I02 – Mobiliario y equipo', I03: 'I03 – Equipo de transporte',
        I04: 'I04 – Equipo de cómputo', I08: 'I08 – Otra maquinaria',
        D01: 'D01 – Honorarios médicos', D02: 'D02 – Gastos médicos incapacidad', D03: 'D03 – Gastos funerales',
        D04: 'D04 – Donativos', D07: 'D07 – Primas seguros médicos', D10: 'D10 – Servicios educativos',
    };
    const POR_REGIMEN = {
        '605': ['S01', 'CP01', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '606': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '608': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '611': ['S01', 'CP01', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '612': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '614': ['S01', 'CP01', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '616': ['S01'],
        '621': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '625': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08', 'D01', 'D02', 'D03', 'D04', 'D07', 'D10'],
        '626': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '601': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '603': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '620': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '622': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '623': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '607': ['S01', 'CP01', 'G01', 'G02', 'G03'], '609': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '610': ['S01', 'CP01'], '615': ['S01', 'CP01'], '624': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'],
        '628': ['S01', 'CP01', 'G01', 'G02', 'G03', 'I01', 'I02', 'I03', 'I04', 'I08'], '630': ['S01', 'CP01'],
    };

    if (regimen) {
        regimen.addEventListener('change', function () {
            const permitidos = POR_REGIMEN[regimen.value] || [];
            uso.innerHTML = '';
            if (!regimen.value) {
                uso.innerHTML = '<option value="">Primero elige tu régimen...</option>';
                usoHint.textContent = 'Las opciones dependen del régimen.';
                return;
            }
            uso.innerHTML = '<option value="">Seleccione...</option>';
            permitidos.forEach(c => { uso.innerHTML += `<option value="${c}">${USOS[c]}</option>`; });
            if (regimen.value === '605') usoHint.textContent = 'Asalariado: para renta normalmente aplica S01.';
            else if (regimen.value === '612' || regimen.value === '626') usoHint.textContent = 'Negocio: lo común es G03.';
            else usoHint.textContent = 'Elige el uso que corresponda.';
        });
    }

    if (rfc) {
        rfc.addEventListener('input', function () {
            rfc.value = rfc.value.toUpperCase();
            const len = rfc.value.trim().length;
            if (len === 13) { rfcHint.textContent = '✓ Persona física (13 caracteres)'; rfcHint.style.color = '#2e7d32'; }
            else if (len === 12) { rfcHint.textContent = '✓ Empresa / persona moral (12 caracteres)'; rfcHint.style.color = '#2e7d32'; }
            else { rfcHint.textContent = '13 caracteres = persona física · 12 = empresa'; rfcHint.style.color = '#888'; }
        });
    }
}

// ============================================
// MODAL DE CANCELACIÓN
// ============================================
function abrirModalCancelar(id, folio) {
    document.getElementById('c_folio_texto').textContent = folio;
    document.getElementById('formCancelar').action = '/admin/facturas/' + id + '/cancelar';
    // Resetear el formulario
    document.getElementById('c_motivo').value = '';
    document.getElementById('c_sustituto_wrap').style.display = 'none';
    document.getElementById('c_ayuda').style.display = 'none';
    document.getElementById('c_sustituto').value = '';
    document.getElementById('c_sustituto').required = false;
    document.getElementById('modalCancelar').style.display = 'block';
}

function cerrarModalCancelar() {
    document.getElementById('modalCancelar').style.display = 'none';
}

// Mostrar ayuda y campo sustituto según el motivo elegido
function inicializarCancelacion() {
    const motivoSelect = document.getElementById('c_motivo');
    if (!motivoSelect) return;

    motivoSelect.addEventListener('change', function () {
        const ayuda = document.getElementById('c_ayuda');
        const sustitutoWrap = document.getElementById('c_sustituto_wrap');
        const sustitutoInput = document.getElementById('c_sustituto');

        const ayudas = {
            '01': 'Usa este motivo cuando la factura tenía un error (RFC, monto, etc.) y YA generaste una factura nueva para reemplazarla. Necesitas el folio fiscal (UUID) de esa nueva factura.',
            '02': 'Usa este motivo cuando la factura se emitió por error (duplicada, cliente equivocado) y NO vas a generar otra en su lugar. Es la cancelación más común y directa.',
            '03': 'Usa este motivo cuando la operación nunca se realizó (el cliente no rentó, se canceló todo).',
            '04': 'Usa este motivo solo para operaciones que ya están relacionadas en una factura global.',
        };

        if (this.value) {
            ayuda.textContent = ayudas[this.value] || '';
            ayuda.style.display = 'block';
        } else {
            ayuda.style.display = 'none';
        }

        // El campo de sustituto (UUID) solo aparece con motivo 01
        if (this.value === '01') {
            sustitutoWrap.style.display = 'block';
            sustitutoInput.required = true;
        } else {
            sustitutoWrap.style.display = 'none';
            sustitutoInput.required = false;
        }
    });
}

// ============================================
// MODAL DE ENVIAR POR CORREO
// ============================================
function abrirModalEnviar(id, folio) {
    document.getElementById('e_folio_texto').textContent = folio;
    document.getElementById('formEnviar').action = '/admin/facturas/' + id + '/enviar';
    document.getElementById('e_correo').value = '';
    document.getElementById('modalEnviar').style.display = 'block';
}

function cerrarModalEnviar() {
    document.getElementById('modalEnviar').style.display = 'none';
}