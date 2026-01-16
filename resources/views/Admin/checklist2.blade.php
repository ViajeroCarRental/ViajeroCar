@extends('layouts.Ventas')

@section('Titulo', 'Checklist – Entrega y Recepción')

{{-- CSS SOLO VISUAL --}}
@section('css-vistaFacturar')
<link rel="stylesheet" href="{{ asset('css/checklist2.css') }}">

@endsection

@section('contenidoFacturar')

<div class="checklist2-container">


    <!-- ENCABEZADO -->
    <header class="cl2-header">

        <div class="cl2-logo">
            <img src="/img/Logotipo Fondo.jpg" alt="Viajero Car Rental">
        </div>

        <div class="cl2-title-block">
            <h1>VIAJERO CAR RENTAL</h1>
            <h2>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</h2>

            <p class="office-info">
                BUGAMBILIAS #7, LOS BENITOS, COLÓN<br>
                QUERÉTARO, Qro. CP 76259<br>
                gerencia-mkt@viajerocar-rental.com<br>
                Tel. 441 690 09 98 / Cel. 442 716 97 93
            </p>
        </div>

        <div class="cl2-ra-box">
            <div class="label">No. Rental Agreement</div>
            <div class="value">-----</div>

            <div class="label small">Fecha de Cambio</div>
            <div class="value small">--/--/---- --:--</div>
        </div>

    </header>


    <!-- COLUMNAS PRINCIPALES -->
    <section class="cl2-columns">

        <!-- COLUMNA IZQUIERDA – AUTO RECIBIDO POR EMPRESA -->
        <div class="cl2-col">
            <h3 class="cl2-section-title">AUTO RECIBIDO POR EMPRESA</h3>

            <table class="cl2-table">
                <tr>
                    <th>CATEGORIA</th>
                    <td>{{ $categoria->codigo ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>TIPO</th>
                    <td>{{ $vehiculo->tipo_servicio ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>MODELO</th>
                    <td>{{ $vehiculo->modelo ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>PLACAS</th>
                    <td>{{ $vehiculo->placa ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>TRANSMISIÓN</th>
                    <td>{{ $vehiculo->transmision ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>FUEL OUT</th>
                    <td>
                     @if(!is_null($vehiculo->gasolina_actual ?? null))
                            {{ $vehiculo->gasolina_actual }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>KILOMETRAJE OUT</th>
                    <td>{{ $vehiculo->kilometraje ?? 'N/A' }}</td>
                </tr>
            </table>

            {{-- DIAGRAMA INTERACTIVO – EMPRESA --}}
            <div class="cl2-car-diagram">
                <div class="cl2-car-svg-box">
                    <svg class="car-svg" viewBox="0 0 800 1280" data-context="empresa">
                        <image href="{{ asset('img/diagrama-carro-danos3.png') }}"
                               x="0" y="0" width="800" height="1280" />

                        {{-- DEFENSA DELANTERA --}}
                        <circle class="point-dot" data-zone="1" cx="400" cy="120" r="26" />
                        <circle class="point-dot" data-zone="2" cx="400" cy="210" r="26" />

                        {{-- COFRE / PARABRISAS --}}
                        <circle class="point-dot" data-zone="5" cx="400" cy="365" r="26" />

                        {{-- COSTADOS FRONTALES --}}
                        <circle class="point-dot" data-zone="3" cx="155" cy="385" r="26" />
                        <circle class="point-dot" data-zone="4" cx="645" cy="385" r="26" />

                        {{-- PUERTAS DELANTERAS --}}
                        <circle class="point-dot" data-zone="6" cx="155" cy="525" r="26" />
                        <circle class="point-dot" data-zone="7" cx="645" cy="525" r="26" />

                        {{-- PUERTAS TRASERAS --}}
                        <circle class="point-dot" data-zone="8" cx="155" cy="685" r="26" />
                        <circle class="point-dot" data-zone="9" cx="645" cy="685" r="26" />

                        {{-- TECHO --}}
                        <circle class="point-dot" data-zone="10" cx="400" cy="640" r="26" />

                        {{-- COSTADOS TRASEROS --}}
                        <circle class="point-dot" data-zone="11" cx="155" cy="845" r="26" />
                        <circle class="point-dot" data-zone="12" cx="645" cy="845" r="26" />

                        {{-- DEFENSA TRASERA --}}
                        <circle class="point-dot" data-zone="13" cx="400" cy="1010" r="26" />

                        {{-- LLANTAS --}}
                        <circle class="point-dot" data-zone="15" cx="117"  cy="458" r="26" />
                        <circle class="point-dot" data-zone="16" cx="682"  cy="458" r="26" />
                        <circle class="point-dot" data-zone="17" cx="117"  cy="908" r="26" />
                        <circle class="point-dot" data-zone="18" cx="682"  cy="908" r="26" />
                    </svg>
                </div>

                <p class="cl2-car-hint">
                    Haz clic en los puntos para registrar daños al recibir el vehículo.
                </p>


                <table class="cl2-danos-table" data-context="empresa">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Daño / Nota</th>
                            <th>Costo</th>
                            <th>Foto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="cl2-danos-empty">
                            <td colspan="5">Sin daños registrados.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="cl2-danos-total" data-context="empresa">
                    Total daños: $0.00 MXN
                </div>
            </div>

            <div class="cl2-sign-box">
                <span>FIRMA</span>
                <div class="line"></div>
                <span class="name">___________________</span>
            </div>
        </div>


        <!-- COLUMNA DERECHA – AUTO ENTREGADO A CLIENTE -->
        <div class="cl2-col">
            <h3 class="cl2-section-title">AUTO ENTREGADO A CLIENTE</h3>

            <table class="cl2-table">
                <tr><th>CATEGORIA</th><td>N/A</td></tr>
                <tr><th>SIZE</th><td>N/A</td></tr>
                <tr><th>TIPO</th><td>N/A</td></tr>
                <tr><th>MODELO</th><td>N/A</td></tr>
                <tr><th>PLACAS</th><td>N/A</td></tr>
                <tr><th>COLOR</th><td>N/A</td></tr>
                <tr><th>TRANSMISIÓN</th><td>N/A</td></tr>
                <tr><th>Capacidad del tanque</th><td>N/A</td></tr>
                <tr><th>FUEL OUT</th><td>N/A</td></tr>
                <tr><th>KILOMETRAJE OUT</th><td>N/A</td></tr>
            </table>

            {{-- DIAGRAMA INTERACTIVO – CLIENTE --}}
            <div class="cl2-car-diagram">
                <div class="cl2-car-svg-box">
                    <svg class="car-svg" viewBox="0 0 800 1280" data-context="cliente">
                        <image href="{{ asset('img/diagrama-carro-danos3.png') }}"
                               x="0" y="0" width="800" height="1280" />

                        {{-- DEFENSA DELANTERA --}}
                        <circle class="point-dot" data-zone="1" cx="400" cy="120" r="26" />
                        <circle class="point-dot" data-zone="2" cx="400" cy="210" r="26" />

                        {{-- COFRE / PARABRISAS --}}
                        <circle class="point-dot" data-zone="5" cx="400" cy="365" r="26" />

                        {{-- COSTADOS FRONTALES --}}
                        <circle class="point-dot" data-zone="3" cx="155" cy="385" r="26" />
                        <circle class="point-dot" data-zone="4" cx="645" cy="385" r="26" />

                        {{-- PUERTAS DELANTERAS --}}
                        <circle class="point-dot" data-zone="6" cx="155" cy="525" r="26" />
                        <circle class="point-dot" data-zone="7" cx="645" cy="525" r="26" />

                        {{-- PUERTAS TRASERAS --}}
                        <circle class="point-dot" data-zone="8" cx="155" cy="685" r="26" />
                        <circle class="point-dot" data-zone="9" cx="645" cy="685" r="26" />

                        {{-- TECHO --}}
                        <circle class="point-dot" data-zone="10" cx="400" cy="640" r="26" />

                        {{-- COSTADOS TRASEROS --}}
                        <circle class="point-dot" data-zone="11" cx="155" cy="845" r="26" />
                        <circle class="point-dot" data-zone="12" cx="645" cy="845" r="26" />

                        {{-- DEFENSA TRASERA --}}
                        <circle class="point-dot" data-zone="13" cx="400" cy="1010" r="26" />

                        {{-- LLANTAS --}}
                        <circle class="point-dot" data-zone="15" cx="117"  cy="458" r="26" />
                        <circle class="point-dot" data-zone="16" cx="682"  cy="458" r="26" />
                        <circle class="point-dot" data-zone="17" cx="117"  cy="908" r="26" />
                        <circle class="point-dot" data-zone="18" cx="682"  cy="908" r="26" />
                    </svg>
                </div>

                <p class="cl2-car-hint">
                    Haz clic en los puntos para registrar daños al entregar el vehículo al cliente.
                </p>

                <table class="cl2-danos-table" data-context="cliente">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Daño / Nota</th>
                            <th>Costo</th>
                            <th>Foto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="cl2-danos-empty">
                            <td colspan="5">Sin daños registrados.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="cl2-danos-total" data-context="cliente">
                    Total daños: $0.00 MXN
                </div>
            </div>

            <div class="cl2-sign-box">
                <span>FIRMA</span>
                <div class="line"></div>
                <span class="name">___________________</span>
            </div>

        </div>

    </section>

</div>

{{-- ================== MODAL GLOBAL PARA DAÑOS ================== --}}
<div id="modalDano">
    <div class="box">
        <h4 id="modalZonaLabel">Zona</h4>
        <div class="sub" id="modalContextoLabel">Contexto</div>

        <label for="tipoDano">Tipo de daño</label>
        <input type="text" id="tipoDano" placeholder="Ej. Golpe leve, rayón, cristal estrellado...">

        <label for="comentarioDano">Comentario</label>
        <textarea id="comentarioDano" placeholder="Describe el daño o alguna observación relevante..."></textarea>

        <label for="costoDano">Costo estimado (MXN)</label>
        <input type="number" id="costoDano" min="0" step="0.01" placeholder="0.00">

        <label for="fotoDano">Fotografía del daño (opcional)</label>
        <input type="file" id="fotoDano" accept="image/*">
        <img id="previewFotoDano" alt="Vista previa del daño">

        <button type="button" id="guardarDano" class="btn-modal btn-save">Guardar daño</button>
        <button type="button" id="cancelarDano" class="btn-modal btn-cancel">Cancelar</button>
    </div>
</div>

{{-- ================== JS DEL DIAGRAMA Y DAÑOS ================== --}}
<script>
    (function () {
        const nombresZonas = {
            1: "Defensa delantera",
            2: "Defensa delantera superior",
            3: "Costado izquierdo frontal",
            4: "Costado derecho frontal",
            5: "Cofre / parabrisas",
            6: "Puerta delantera izquierda",
            7: "Puerta delantera derecha",
            8: "Puerta trasera izquierda",
            9: "Puerta trasera derecha",
            10: "Techo",
            11: "Costado trasero izquierdo",
            12: "Costado trasero derecho",
            13: "Defensa trasera",
            15: "Llanta delantera izquierda",
            16: "Llanta delantera derecha",
            17: "Llanta trasera izquierda",
            18: "Llanta trasera derecha",
        };

        // Estructura en memoria para daños
        const danos = {
            empresa: [],
            cliente: []
        };

        let contextoActual = null;
        let zonaActual = null;
        let puntoActual = null;
        let fotoTemporalUrl = null;

        const modal = document.getElementById("modalDano");
        const modalZonaLabel = document.getElementById("modalZonaLabel");
        const modalContextoLabel = document.getElementById("modalContextoLabel");

        const tipoInput = document.getElementById("tipoDano");
        const comentarioInput = document.getElementById("comentarioDano");
        const costoInput = document.getElementById("costoDano");
        const fotoInput = document.getElementById("fotoDano");
        const previewFoto = document.getElementById("previewFotoDano");

        const btnGuardar = document.getElementById("guardarDano");
        const btnCancelar = document.getElementById("cancelarDano");

        // Inicializar listeners en los SVG
        document.querySelectorAll(".car-svg").forEach(svg => {
            const contexto = svg.dataset.context; // empresa / cliente

            svg.querySelectorAll(".point-dot").forEach(circle => {
                circle.addEventListener("click", () => {
                    zonaActual = circle.dataset.zone;
                    contextoActual = contexto;
                    puntoActual = circle;

                    const nombreZona = nombresZonas[zonaActual] || ("Zona " + zonaActual);
                    modalZonaLabel.textContent = nombreZona;

                    modalContextoLabel.textContent = contexto === "empresa"
                        ? "AUTO RECIBIDO POR EMPRESA"
                        : "AUTO ENTREGADO A CLIENTE";

                    // Limpiar campos
                    tipoInput.value = "";
                    comentarioInput.value = "";
                    costoInput.value = "";
                    fotoInput.value = "";

                    if (fotoTemporalUrl) {
                        URL.revokeObjectURL(fotoTemporalUrl);
                        fotoTemporalUrl = null;
                    }
                    previewFoto.style.display = "none";

                    modal.style.display = "flex";
                });
            });
        });

        // Vista previa de foto
        fotoInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                if (fotoTemporalUrl) {
                    URL.revokeObjectURL(fotoTemporalUrl);
                }
                fotoTemporalUrl = URL.createObjectURL(file);
                previewFoto.src = fotoTemporalUrl;
                previewFoto.style.display = "block";
            } else {
                previewFoto.style.display = "none";
                if (fotoTemporalUrl) {
                    URL.revokeObjectURL(fotoTemporalUrl);
                    fotoTemporalUrl = null;
                }
            }
        });

        function cerrarModal() {
            modal.style.display = "none";
            contextoActual = null;
            zonaActual = null;
            puntoActual = null;
        }

        btnCancelar.addEventListener("click", cerrarModal);

        // Cerrar modal al hacer clic fuera
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                cerrarModal();
            }
        });

        // Guardar daño en memoria y refrescar tabla
        btnGuardar.addEventListener("click", () => {
            if (!contextoActual || !zonaActual) {
                cerrarModal();
                return;
            }

            const tipo = (tipoInput.value || "").trim();
            const comentario = (comentarioInput.value || "").trim();
            const costo = parseFloat(costoInput.value || "0") || 0;

            const id = Date.now() + Math.floor(Math.random() * 1000);
            const zonaNombre = nombresZonas[zonaActual] || ("Zona " + zonaActual);

            danos[contextoActual].push({
                id,
                zona: zonaActual,
                zonaNombre,
                tipo,
                comentario,
                costo,
                foto: fotoTemporalUrl ? fotoTemporalUrl : null
            });

            renderTabla(contextoActual);
            cerrarModal();
        });

        // Render de tablas
        function renderTabla(contexto) {
            const tabla = document.querySelector(`.cl2-danos-table[data-context="${contexto}"]`);
            const tbody = tabla ? tabla.querySelector("tbody") : null;
            const totalEl = document.querySelector(`.cl2-danos-total[data-context="${contexto}"]`);

            if (!tbody || !totalEl) return;

            tbody.innerHTML = "";
            const lista = danos[contexto];
            let total = 0;

            if (!lista.length) {
                const trEmpty = document.createElement("tr");
                trEmpty.classList.add("cl2-danos-empty");
                trEmpty.innerHTML = `<td colspan="5">Sin daños registrados.</td>`;
                tbody.appendChild(trEmpty);
            } else {
                lista.forEach(d => {
                    total += d.costo;

                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${d.zonaNombre}</td>
                        <td>${d.tipo || d.comentario || "—"}</td>
                        <td>$${d.costo.toFixed(2)}</td>
                        <td class="cl2-dano-foto-flag">${d.foto ? "📷" : "—"}</td>
                        <td>
                            <button type="button"
                                    class="cl2-dano-delete"
                                    data-id="${d.id}"
                                    data-context="${contexto}">
                                ✕
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            totalEl.textContent = "Total daños: $" + total.toFixed(2) + " MXN";

            // Actualizar puntos seleccionados (si hay daño en esa zona)
            ["empresa", "cliente"].forEach(ctx => {
                document.querySelectorAll(`.car-svg[data-context="${ctx}"] .point-dot`).forEach(circ => {
                    const z = circ.dataset.zone;
                    const hayDano = danos[ctx].some(d => String(d.zona) === String(z));
                    circ.classList.toggle("selected", hayDano);
                });
            });
        }

        // Eliminar daño desde tabla
        document.addEventListener("click", (e) => {
            const btn = e.target;
            if (!btn.classList.contains("cl2-dano-delete")) return;

            const id = btn.dataset.id;
            const ctx = btn.dataset.context;

            danos[ctx] = danos[ctx].filter(d => String(d.id) !== String(id));
            renderTabla(ctx);
        });
    })();
</script>

@endsection
