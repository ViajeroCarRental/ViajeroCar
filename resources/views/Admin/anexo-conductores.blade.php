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
        <p class="section-sub" id="subConductores">
            Revise la información de los conductores adicionales autorizados para conducir el vehículo.
        </p>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: LISTA DE CONDUCTORES   -->
    <!-- =============================== -->
    <section class="card-block">
        <div class="block-header">
            <h3 class="block-title">Conductores adicionales registrados</h3>
            <p class="block-subtitle">
                La tabla muestra todos los conductores adicionales registrados para este contrato.
            </p>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre (Name)</th>
                    <th>No. Licencia</th>
                    <th>Firma del Conductor</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>

            <tbody id="tbodyConductores">
                @forelse($conductores as $c)
                    @php
                        $nombreCompleto = trim(($c->nombres ?? '') . ' ' . ($c->apellidos ?? ''));
                    @endphp
                    <tr data-id-conductor="{{ $c->id_conductor }}">
                        {{-- Nombre --}}
                        <td>
                            {{ $nombreCompleto ?: '---' }}
                        </td>

                        {{-- Licencia --}}
                        <td>
                            {{ $c->numero_licencia ?? '---' }}
                        </td>

                        {{-- Firma del conductor --}}
                        <td class="img-cell firma-col">
                            @if(!empty($c->firma_conductor))
                                <img src="{{ asset($c->firma_conductor) }}"
                                     class="doc-thumb"
                                     alt="Firma conductor">
                            @else
                                <span class="no-img">Firma pendiente</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="actions">
                            {{-- Botón para firmar (lápiz) --}}
                            <button type="button"
                                    class="btn-icon btn-firmar-conductor"
                                    data-id="{{ $c->id_conductor }}"
                                    data-nombre="{{ $nombreCompleto }}">
                                <i class="fas fa-pen"></i>
                            </button>

                            {{-- Botón para eliminar --}}
                            <form method="POST"
                                  action="{{ route('anexo.eliminar', $c->id_conductor) }}"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn-icon btn-delete-conductor"
                                        type="submit"
                                        title="Eliminar conductor"
                                        onclick="return confirm('¿Eliminar al conductor adicional {{ $nombreCompleto ?: 'sin nombre' }} del contrato?');">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-row">
                            No hay conductores adicionales registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <!-- =============================== -->
    <!-- BLOQUE: FIRMAS DE CONDUCTORES  -->
    <!-- =============================== -->
    <section class="card-block">
      <div class="block-header">
        <h3 class="block-title">Firmas</h3>
        <p class="block-subtitle">
          Haz clic en el ícono de lápiz de la fila del conductor para capturar su firma digital.
        </p>
      </div>

      <div class="firmas">
        <div class="firma-item">
          <p><b id="labelFirmaConductor">FIRMA DE CONDUCTOR ADICIONAL:</b></p>
          <p class="small-muted">
              La etiqueta se actualizará con el nombre del conductor seleccionado.
          </p>
        </div>
      </div>
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

            {{-- Firma arrendador --}}
<div class="signature-card">
    @if($contrato->firma_arrendador)
        <img src="{{ $contrato->firma_arrendador }}" class="signature-image" alt="Firma arrendador">
    @else
        <span style="opacity:.6;font-size:.85rem">
            Firma pendiente
        </span>
    @endif

    <div class="sig-line"></div>
    <p class="sig-label">Firma del Arrendador(a)</p>

</div>


        </div>
    </section>

   {{-- =============================== --}}
{{-- BOTÓN ENVIAR ANEXOS            --}}
{{-- =============================== --}}
<div class="anexo-actions" style="text-align:center; margin-top: 1.5rem; margin-bottom: 1rem;">

    <form id="formEnviarAnexos"
          method="POST"
          action="{{ route('anexo.enviarAnexos', $contrato->id_contrato) }}"
          style="display:inline-block;">
        @csrf

        <button type="submit"
                class="btn btn-red"
                id="btnEnviarAnexos">
            Enviar anexos por correo
        </button>
    </form>

</div>



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
        <h3 id="tituloModalConductor">Firma del Conductor Adicional</h3>
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

    const baseAsset = "{{ asset('') }}"; // ej. https://tudominio.com/

    /* ==============================================
       🔔 MENSAJES DE LARAVEL → ALERTIFY
       (cuando regresas con ->with('ok') / ->with('error'))
    ============================================== */
    @if(session('ok'))
        alertify.success("{{ session('ok') }}");
    @endif

    @if(session('error'))
        alertify.error("{{ session('error') }}");
    @endif

    @if($errors->any())
        alertify.error("Hay errores en la información enviada, revisa los datos.");
    @endif

    /* ==========================
       FIRMA ARRENDADOR
    =========================== */
    const modalA   = document.getElementById('modalFirma');
    const btnOpenA = document.getElementById('btnFirmar');

    let signaturePadA = null;
    const canvasA = document.getElementById('signature-pad');
    if (canvasA && window.SignaturePad) {
        signaturePadA = new SignaturePad(canvasA);
    }

    if (btnOpenA && modalA && signaturePadA) {
        btnOpenA.addEventListener('click', () => {
            modalA.style.display = 'flex';
            signaturePadA.clear();
        });

        const btnClearA = document.getElementById('clear-signature');
        const btnSaveA  = document.getElementById('save-signature');

        if (btnClearA) {
            btnClearA.addEventListener('click', () => signaturePadA.clear());
        }

        if (btnSaveA) {
            btnSaveA.addEventListener('click', () => {
                if (signaturePadA.isEmpty()) {
                    alertify.warning('Por favor realiza tu firma.');
                    return;
                }

                const dataURL    = signaturePadA.toDataURL("image/png");
                const idContrato = btnOpenA.dataset.contrato;

                alertify.message('Guardando firma de arrendador...');

                fetch("{{ route('anexo.guardarFirma') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        id_contrato: idContrato,
                        firma: dataURL
                    })
                })
                .then(res => res.json())
                .then(resp => {
                    if (!resp.ok) {
                        alertify.error(resp.error || 'No se pudo guardar la firma del arrendador.');
                        return;
                    }
                    alertify.success('Firma de arrendador guardada correctamente.');
                    modalA.style.display = 'none';
                    window.location.reload();
                })
                .catch(() => {
                    alertify.error('Error de comunicación con el servidor al guardar la firma de arrendador.');
                });
            });
        }
    }

    /* ==========================
       FIRMA DE CONDUCTORES
       (tabla con lápiz por fila)
    =========================== */

    const modalC         = document.getElementById('modalFirmaConductor');
    const canvasC        = document.getElementById('signature-pad-conductor');
    const labelFirma     = document.getElementById('labelFirmaConductor');
    const subConductores = document.getElementById('subConductores');
    const tituloModalC   = document.getElementById('tituloModalConductor');

    let signaturePadC = null;
    if (canvasC && window.SignaturePad) {
        signaturePadC = new SignaturePad(canvasC);
    }

    let currentConductorId      = null;
    let currentConductorNombre  = '';
    let currentConductorRow     = null;

    // Botones lápiz por fila
    const btnsFirmar = document.querySelectorAll('.btn-firmar-conductor');

    if (btnsFirmar && modalC && signaturePadC) {
        btnsFirmar.forEach(btn => {
            btn.addEventListener('click', () => {
                currentConductorId     = btn.dataset.id;
                currentConductorNombre = btn.dataset.nombre || 'Conductor adicional';
                currentConductorRow    = btn.closest('tr');

                if (labelFirma) {
                    labelFirma.textContent = 'FIRMA DE ' + currentConductorNombre.toUpperCase() + ':';
                }
                if (subConductores) {
                    subConductores.textContent = 'Revisando conductor adicional: ' + currentConductorNombre;
                }
                if (tituloModalC) {
                    tituloModalC.textContent = 'Firma de ' + currentConductorNombre;
                }

                signaturePadC.clear();
                modalC.style.display = 'flex';
            });
        });
    }

    // Botones de limpiar / guardar del modal del conductor
    const btnClearC = document.getElementById('clear-conductor');
    const btnSaveC  = document.getElementById('save-conductor');

    if (btnClearC && signaturePadC) {
        btnClearC.addEventListener('click', () => signaturePadC.clear());
    }

    if (btnSaveC && signaturePadC) {
        btnSaveC.addEventListener('click', () => {
            if (signaturePadC.isEmpty()) {
                alertify.warning('El conductor debe firmar.');
                return;
            }

            if (!currentConductorId) {
                alertify.error('No hay conductor seleccionado.');
                return;
            }

            const dataURL = signaturePadC.toDataURL("image/png");

            alertify.message('Guardando firma de ' + (currentConductorNombre || 'conductor') + '...');

            fetch("{{ route('anexo.guardarFirmaConductor') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    id_conductor: currentConductorId,
                    firma: dataURL
                })
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.ok) {
                    alertify.success('Firma guardada correctamente para ' + (currentConductorNombre || 'conductor') + '.');

                    // Actualizar la celda de firma SOLO de esta fila
                    if (resp.ruta_firma && currentConductorRow) {
                        const celdaFirma = currentConductorRow.querySelector('.firma-col');
                        if (celdaFirma) {
                            celdaFirma.innerHTML = '';

                            const img = document.createElement('img');
                            img.src = baseAsset + resp.ruta_firma;
                            img.className = 'doc-thumb';
                            img.alt = 'Firma del conductor';

                            celdaFirma.appendChild(img);
                        }
                    }

                    modalC.style.display = 'none';
                    currentConductorId     = null;
                    currentConductorNombre = '';
                    currentConductorRow    = null;

                } else {
                    alertify.error(resp.error || 'Ocurrió un problema al guardar la firma del conductor.');
                }
            })
            .catch(() => {
                alertify.error('Error de comunicación con el servidor al guardar la firma del conductor.');
            });
        });
    }

    // Cerrar modales al hacer clic fuera
    window.addEventListener('click', (e) => {
        if (e.target === modalC) {
            modalC.style.display = 'none';
            currentConductorId     = null;
            currentConductorNombre = '';
            currentConductorRow    = null;
        }
        if (e.target === modalA) {
            modalA.style.display = 'none';
        }
    });

    /* =============================================
       📧 ENVÍO DE ANEXOS POR CORREO
       - Mensaje "enviando..."
       - Luego Laravel regresa con ->with('ok') y
         lo mostramos arriba con alertify.success
    ============================================== */

    const formEnviarAnexos = document.getElementById('formEnviarAnexos');
    const btnEnviarAnexos  = document.getElementById('btnEnviarAnexos');

    if (formEnviarAnexos && btnEnviarAnexos) {
        formEnviarAnexos.addEventListener('submit', function (e) {
            // Opcional: validar que haya al menos un conductor en la tabla
            const filas = document.querySelectorAll('#tbodyConductores tr');
            const hayConductores = filas.length > 0 && !filas[0].classList.contains('empty-row');

            if (!hayConductores) {
                e.preventDefault();
                alertify.warning('No hay conductores adicionales registrados para enviar anexos.');
                return;
            }

            // Mensaje mientras se procesa el cambio / envío de correo
            alertify.message('Se está procesando el anexo y enviando los correos, por favor espera...');
            btnEnviarAnexos.disabled = true;
        });
    }

});
</script>
@endsection


