@extends('layouts.Ventas')

@section('Titulo', 'Anexo – Conductor Adicional')

{{-- CSS --}}
@section('css-vistaFacturar')
<link rel="stylesheet" href="{{ asset('css/anexo.css') }}">
@endsection

@section('contenidoFacturar')

<div class="document-wrapper">

    <!-- ENCABEZADO -->
    <header class="doc-header">
        <div class="header-left">
            <img src="/img/logo-viajero.png" class="logo">
        </div>

        <div class="header-right">
            <h1>ANEXO</h1>
            <div class="agreement-box<">
                <span class="ag-label">No. Rental Agreement</span>
                <span class="ag-value">{{ $reservacion->id_reservacion ?? '---' }}</span>
            </div>
        </div>
    </header>

    <h2 class="section-title">Autorización para Conductor Adicional Aceptado</h2>
    <p class="section-sub">
        Se aplicarán cargos adicionales según los términos del contrato de renta.
    </p>

    <!-- TABLA -->
    <table class="styled-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Edad</th>
                <th>Licencia</th>
                <th>Vence</th>
                <th>Documento</th>
                <th></th>
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
                    @if($c->imagen_licencia)
                        <a href="{{ asset($c->imagen_licencia) }}" target="_blank">
                            <img src="{{ asset($c->imagen_licencia) }}" class="doc-thumb">
                        </a>
                    @else
                        <span class="no-img">Sin imagen</span>
                    @endif
                </td>

                <td class="actions">
                    <form action="{{ route('anexo.eliminar', $c->id_conductor) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn-delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-row">No hay conductores adicionales registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FORMULARIO -->
    <div class="form-card">
        <h3>Agregar Conductor Adicional</h3>

        <form method="POST" action="{{ route('anexo.guardar') }}" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="id_reservacion" value="{{ $reservacion->id_reservacion ?? 0 }}">

            <div class="form-row">
                <label>Nombre</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="form-row">
                <label>Edad</label>
                <input type="number" name="edad">
            </div>

            <div class="form-row">
                <label>Licencia</label>
                <input type="text" name="licencia" required>
            </div>

            <div class="form-row">
                <label>Vence</label>
                <input type="text" name="vence">
            </div>

            <div class="form-row">
                <label>Documento (INE o Licencia)</label>
                <input type="file" name="imagen_licencia" accept="image/*">
            </div>

            <button class="btn-primary" type="submit">Agregar Conductor</button>
        </form>
    </div>

    <!-- TEXTO LEGAL -->
    <section class="legal-section">
        <h3>Declaración</h3>
        <p>
            Certifico que tengo la mayoría de edad y poseo una licencia de conducir válida. Acepto ser responsable de los cargos,
            daños, responsabilidades y términos estipulados dentro del Contrato de Renta, incluyendo cualquier obligación derivada
            del uso del vehículo autorizado.
        </p>

        <p>
            Entiendo y acepto que no podré solicitar cambio de vehículo ni extensión de renta mediante este documento.
        </p>

        <p>
            Autorizo al conductor(es) adicional(es) arriba indicado(s) para conducir el vehículo conforme a los términos y condiciones
            establecidos en el contrato original.
        </p>
    </section>

    <!-- FIRMA -->
    <div class="signature-section">

        @if($reservacion->firma_arrendador)
            <img src="{{ asset($reservacion->firma_arrendador) }}"
                 style="width:300px; display:block; margin:0 auto 10px;">
        @endif

        <div class="sig-line"></div>
        <p>Firma del Arrendador(a)</p>

        <button id="btnFirmar" class="btn-primary" style="margin-top:20px;">
            Firmar Documento
        </button>
    </div>

</div>

<!-- MODAL DE FIRMA -->
<div id="modalFirma" class="modal-firma">
    <div class="firma-content">
        <h3>Firmar Documento</h3>

        <canvas id="signature-pad" width="500" height="200"></canvas>

        <div class="firma-buttons">
            <button id="clear-signature" class="btn-del">Limpiar</button>
            <button id="save-signature" class="btn-primary">Guardar Firma</button>
        </div>
    </div>
</div>

@endsection

@section('js-vistaFacturar')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modalFirma');
    const btnOpen = document.getElementById('btnFirmar');

    btnOpen.addEventListener('click', () => modal.style.display = 'flex');

    // Canvas firma
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    document.getElementById('clear-signature').onclick = () => signaturePad.clear();

    document.getElementById('save-signature').onclick = () => {
        if (signaturePad.isEmpty()) {
            alert("Por favor realiza una firma primero.");
            return;
        }

        const dataURL = signaturePad.toDataURL("image/png");

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
        .then(data => {
            alert('Firma guardada correctamente');
            modal.style.display = 'none';
            window.location.reload();
        });
    };

});
</script>
@endsection
