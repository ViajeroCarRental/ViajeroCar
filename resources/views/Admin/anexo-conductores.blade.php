@extends('layouts.Ventas')

@section('Titulo', 'Anexo – Conductor Adicional')

@section('css-vistaFacturar')
<link rel="stylesheet" href="{{ asset('css/anexo.css') }}">
@endsection

@section('contenidoFacturar')

<div class="document-wrapper">

    <!-- =============================== -->
    <!-- ENCABEZADO DOCUMENTO           -->
    <!-- =============================== -->
    <header class="doc-header">
        <div class="header-left">
            <img src="{{ asset('/img/Logotipo Fondo.jpg') }}" class="big-header-logo">


            <div class="company-info">
                <p>Viajero Car Rental</p>
                <p>Anexo de contrato – Conductor adicional</p>
            </div>
        </div>

        <div class="header-right">
            <div class="doc-meta">
                <span class="doc-label">Anexo de Contrato</span>
                <h1 class="doc-title">Autorización de Conductor Adicional</h1>

                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">No. Rental Agreement</span>
                        <span class="meta-value">{{ $reservacion->id_reservacion ?? '---' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Fecha</span>
                        <span class="meta-value">____ / ____ / ______</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- =============================== -->
    <!-- INTRO / NOTA                   -->
    <!-- =============================== -->
    <section class="intro-section">
        <h2 class="section-title">Autorización para Conductor Adicional Aceptado</h2>
        <p class="section-sub">
            Nota: Se aplicarán cargos al presente contrato de renta por concepto de conductor(es) adicional(es).
        </p>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: LISTA DE CONDUCTORES   -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Conductores adicionales registrados</h3>
            <p class="block-subtitle">
                Revise la información de los conductores adicionales autorizados para conducir el vehículo.
            </p>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre (Name)</th>
                    <th>Años (Age)</th>
                    <th>No. Licencia</th>
                    <th>Vence (Expira)</th>
                    <th>Firma del Conductor</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($conductores as $c)
                    <tr>
                        <td>{{ $c->nombre }}</td>
                        <td>{{ $c->edad }}</td>
                        <td>{{ $c->licencia }}</td>
                        <td>{{ $c->vence }}</td>

                        <td class="img-cell">
                            @if($c->firma_conductor)
                                <img src="{{ asset($c->firma_conductor) }}" class="doc-thumb" alt="Firma conductor">
                            @else
                                <span class="no-img">Sin firma</span>
                            @endif
                        </td>

                        <td class="actions">
                            <form action="{{ route('anexo.eliminar', $c->id_conductor) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn-icon" title="Eliminar conductor">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-row">
                            No hay conductores adicionales registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: FORMULARIO NUEVO       -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Agregar conductor adicional</h3>
            <p class="block-subtitle">
                Capture los datos del nuevo conductor adicional que será autorizado para conducir el vehículo.
            </p>
        </div>

        <form method="POST" action="{{ route('anexo.guardar') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_reservacion" value="{{ $reservacion->id_reservacion ?? 0 }}">

            <div class="form-grid">
                <div class="form-row form-row-lg">
                    <label>Nombre completo del conductor</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-row form-row-sm">
                    <label>Edad</label>
                    <input type="number" name="edad" min="18" required>
                </div>

                <div class="form-row form-row-md">
                    <label>No. de licencia</label>
                    <input type="text" name="licencia" required>
                </div>

                <div class="form-row form-row-md">
                    <label>Vence (Expira)</label>
                    <input type="text" name="vence" placeholder="Permanente o fecha" required>
                </div>

                <div class="form-row form-row-lg">
                    <label>Documento (INE / Licencia / Pasaporte)</label>
                    <input type="file" name="documento" accept="image/*,application/pdf" required>
                </div>

                <div class="form-row form-row-lg">
                    <label>Firma del conductor adicional</label>
                    <div class="firma-inline">
                        <button type="button" id="btnFirmaConductor" class="btn-secondary">
                            Capturar firma
                        </button>
                        <span class="firma-hint">
                            La firma quedará ligada al registro del conductor en la tabla superior.
                        </span>
                    </div>
                    <input type="hidden" name="firma_conductor" id="firma_conductor">
                </div>

                <div class="form-actions">
                    <button class="btn-primary" type="submit">
                        Agregar conductor
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: TEXTO LEGAL            -->
    <!-- =============================== -->
    <section class="card-block legal-block">
        <div class="block-header">
            <h3 class="block-title">Declaraciones y aceptación</h3>
            <p class="block-subtitle">
                El conductor adicional y el arrendador reconocen y aceptan las condiciones descritas a continuación.
            </p>
        </div>

        <section class="legal-section">
             <p>
                <strong>CON MI FIRMA:</strong> Certifico que tengo la mayoría de edad y que poseo una licencia de conducir
                vigente y válida. Acepto que seré conjunta y solidariamente responsable de las obligaciones del titular
                del Contrato de Renta, incluyendo la obligación de indemnizar sin límite según los términos del mismo.
            </p>

            <p>
                Entiendo y acepto que mediante este documento no podré solicitar cambio de vehículo ni extensión del
                periodo de renta. Cualquier modificación al Contrato de Renta deberá realizarse por los canales formales
                establecidos por la arrendadora.
            </p>

            <p>
                Autorizo expresamente al conductor(es) adicional(es) arriba indicado(s) para conducir el vehículo
                amparado por el Contrato de Renta, bajo los mismos términos, condiciones, restricciones y responsabilidades
                aplicables al titular del contrato.
            </p>
        </section>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: FIRMA ÚNICA            -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Firma del anexo</h3>
            <p class="block-subtitle">
                La firma del arrendador se realizará de forma digital en el sistema.
            </p>
        </div>

        <div class="signatures-wrapper">

            <!-- Firma arrendador -->
            <div class="signature-card">
                @if($reservacion->firma_arrendador)
                    <img src="{{ asset($reservacion->firma_arrendador) }}" class="signature-image" alt="Firma arrendador">
                @endif

                <div class="sig-line"></div>
                <p class="sig-label">Firma del Arrendador(a)</p>

                <button id="btnFirmar" class="btn-primary" type="button" style="margin-top: 10px;">
                    Firmar documento
                </button>
            </div>

        </div>
    </section>

</div>

<!-- =============================== -->
<!-- MODALES DE FIRMA               -->
<!-- =============================== -->

<!-- Modal firma arrendador -->
<div id="modalFirma" class="modal-firma">
    <div class="firma-content">
        <h3>Firmar Arrendador</h3>
        <canvas id="signature-pad" width="500" height="200"></canvas>

        <div class="firma-buttons">
            <button id="clear-signature" class="btn-del">Limpiar</button>
            <button id="save-signature" class="btn-primary">Guardar firma</button>
        </div>
    </div>
</div>

<!-- Modal firma conductor -->
<div id="modalFirmaConductor" class="modal-firma">
    <div class="firma-content">
        <h3>Firma del Conductor Adicional</h3>
        <canvas id="signature-pad-conductor" width="500" height="200"></canvas>

        <div class="firma-buttons">
            <button id="clear-conductor" class="btn-del">Limpiar</button>
            <button id="save-conductor" class="btn-primary">Guardar firma</button>
        </div>
    </div>
</div>

@endsection

@section('js-vistaFacturar')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ==========================
       FIRMA ARRENDADOR
    =========================== */
    const modalA   = document.getElementById('modalFirma');
    const btnOpenA = document.getElementById('btnFirmar');

    if (btnOpenA && modalA) {
        btnOpenA.addEventListener('click', () => {
            modalA.style.display = 'flex';
        });
    }

    const canvasA = document.getElementById('signature-pad');
    const signaturePadA = new SignaturePad(canvasA);

    document.getElementById('clear-signature').onclick = () => signaturePadA.clear();

    document.getElementById('save-signature').onclick = () => {
        if (signaturePadA.isEmpty()) {
            alert("Por favor realiza tu firma.");
            return;
        }

        const dataURL = signaturePadA.toDataURL("image/png");

        fetch("{{ route('anexo.guardarFirma') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id_reservacion: "{{ $reservacion->id_reservacion }}",
                firma: dataURL
            })
        })
        .then(res => res.json())
        .then(() => {
            alert('Firma guardada correctamente');
            modalA.style.display = 'none';
            window.location.reload();
        });
    };

    /* ==========================
       FIRMA CONDUCTOR
    =========================== */
    const modalC     = document.getElementById('modalFirmaConductor');
    const btnOpenC   = document.getElementById('btnFirmaConductor');
    const inputFirma = document.getElementById('firma_conductor');

    const canvasC = document.getElementById('signature-pad-conductor');
    const signaturePadC = new SignaturePad(canvasC);

    if (btnOpenC && modalC) {
        btnOpenC.addEventListener('click', () => {
            modalC.style.display = 'flex';
            signaturePadC.clear();
        });
    }

    document.getElementById('clear-conductor').onclick = () => signaturePadC.clear();

    document.getElementById('save-conductor').onclick = () => {
        if (signaturePadC.isEmpty()) {
            alert("El conductor debe firmar.");
            return;
        }

        const dataURL = signaturePadC.toDataURL("image/png");
        inputFirma.value = dataURL;

        alert("✔ Firma del conductor guardada en el formulario");
        modalC.style.display = 'none';
    };

});
</script>
@endsection
