@php
    $folio = str_pad($convenio->id_convenio ?? 0, 3, '0', STR_PAD_LEFT);

    $fecha = !empty($convenio->created_at)
        ? \Carbon\Carbon::parse($convenio->created_at)->locale('es')->translatedFormat('d/M/Y')
        : now()->locale('es')->translatedFormat('d/M/Y');
@endphp

<header class="pdf-header">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('img/Logo5.png') }}" class="logo-img">
            </td>

            <td class="header-info">
                <div class="pdf-title">Convenio Member Prefer</div>
                <div class="pdf-subtitle">Grupo Viajero Car Rental</div>

                <div class="company-data">
                    Tel. {{ $telefonoEmpresa ?? '442 716 9793' }}<br>
                    Dirección: {{ $direccionEmpresa ?? 'Business Center INNERA Central Park, Armando Birlain Shaffler 2001 Torre2, 9C, 76090 Santiago de Querétaro, Qro.' }}
                </div>
            </td>

            <td class="header-folio">
                <div class="folio-label">Folio</div>
                <div class="folio-number">#{{ $folio }}</div>
                <div class="folio-date">{{ $fecha }}</div>
            </td>
        </tr>
    </table>
</header>