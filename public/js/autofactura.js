document.addEventListener('DOMContentLoaded', function () {
    const regimenSelect = document.getElementById('regimen_fiscal');
    const usoSelect     = document.getElementById('uso_cfdi');
    const rfcInput      = document.getElementById('rfc');
    const usoHint       = document.getElementById('uso-hint');

    // Catálogo de usos de CFDI
    const USOS = {
        S01:  'S01 – Sin efectos fiscales',
        CP01: 'CP01 – Pagos',
        G01:  'G01 – Adquisición de mercancías',
        G02:  'G02 – Devoluciones, descuentos o bonificaciones',
        G03:  'G03 – Gastos en general',
        I01:  'I01 – Construcciones',
        I02:  'I02 – Mobiliario y equipo de oficina',
        I03:  'I03 – Equipo de transporte',
        I04:  'I04 – Equipo de cómputo',
        I08:  'I08 – Otra maquinaria y equipo',
        D01:  'D01 – Honorarios médicos y hospitalarios',
        D02:  'D02 – Gastos médicos por incapacidad',
        D03:  'D03 – Gastos funerales',
        D04:  'D04 – Donativos',
        D07:  'D07 – Primas de seguros de gastos médicos',
        D10:  'D10 – Pagos por servicios educativos',
    };

    // Qué usos permite cada régimen (según catálogo c_UsoCFDI del SAT)
    const USOS_POR_REGIMEN = {
        // Personas físicas
        '605': ['S01','CP01','D01','D02','D03','D04','D07','D10'],
        '606': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08','D01','D02','D03','D04','D07','D10'],
        '608': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08','D01','D02','D03','D04','D07','D10'],
        '611': ['S01','CP01','D01','D02','D03','D04','D07','D10'],
        '612': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08','D01','D02','D03','D04','D07','D10'],
        '614': ['S01','CP01','D01','D02','D03','D04','D07','D10'],
        '616': ['S01'],
        '621': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08','D01','D02','D03','D04','D07','D10'],
        '625': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08','D01','D02','D03','D04','D07','D10'],
        '626': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
        // Personas morales
        '601': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
        '603': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
        '620': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
        '622': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
        '623': ['S01','CP01','G01','G02','G03','I01','I02','I03','I04','I08'],
    };

    function actualizarUsos() {
        const regimen = regimenSelect.value;
        const permitidos = USOS_POR_REGIMEN[regimen] || [];

        usoSelect.innerHTML = '';

        if (!regimen) {
            usoSelect.innerHTML = '<option value="">Primero elige tu régimen...</option>';
            usoHint.textContent = 'Las opciones dependen de tu régimen fiscal.';
            return;
        }

        if (permitidos.length === 0) {
            usoSelect.innerHTML = '<option value="">Sin usos disponibles</option>';
            return;
        }

        usoSelect.innerHTML = '<option value="">Seleccione...</option>';
        permitidos.forEach(function (codigo) {
            const opt = document.createElement('option');
            opt.value = codigo;
            opt.textContent = USOS[codigo];
            usoSelect.appendChild(opt);
        });

        if (regimen === '605') {
            usoHint.textContent = 'Como asalariado, para renta de auto normalmente aplica S01.';
        } else if (regimen === '612' || regimen === '626') {
            usoHint.textContent = 'Para negocios, lo común es G03 (Gastos en general).';
        } else {
            usoHint.textContent = 'Elige el uso que corresponda a tu situación.';
        }
    }

    function validarLongitudRfc() {
        const len = rfcInput.value.trim().length;
        const hint = document.getElementById('rfc-hint');
        if (len === 13) {
            hint.textContent = '✓ RFC de persona física (13 caracteres)';
            hint.style.color = '#2e7d32';
        } else if (len === 12) {
            hint.textContent = '✓ RFC de empresa / persona moral (12 caracteres)';
            hint.style.color = '#2e7d32';
        } else {
            hint.textContent = '13 caracteres = persona física · 12 = empresa';
            hint.style.color = '#888';
        }
    }

    regimenSelect.addEventListener('change', actualizarUsos);
    rfcInput.addEventListener('input', function () {
        rfcInput.value = rfcInput.value.toUpperCase();
        validarLongitudRfc();
    });

    actualizarUsos();
});