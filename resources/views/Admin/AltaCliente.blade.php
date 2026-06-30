@extends('layouts.Ventas')

@section('Titulo', 'Alta Member Prefer')

@section('css-vistaAltaCliente')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/AltaCliente.css') }}">
@endsection

@section('contenidoAltaCliente')
@php
    $categorias = ['Económico', 'Compacto', 'Intermedio', 'SUV', 'Van'];

    $protecciones = [
        ['name' => 'Protección 1', 'price' => 0, 'guarantee' => 0],
        ['name' => 'Protección 2', 'price' => 250, 'guarantee' => 5000],
        ['name' => 'Protección 3', 'price' => 450, 'guarantee' => 8000],
        ['name' => 'Protección 4', 'price' => 650, 'guarantee' => 12000],
        ['name' => 'Protección 5', 'price' => 850, 'guarantee' => 18000],
    ];
@endphp

<main class="main alta-member">

    <div class="header">
        <div>
            <h1 class="h1">Alta Prefer Member</h1>
        </div>

        <div class="row">
            <button class="btn ghost" id="btnBack" type="button">Regresar</button>
        </div>
    </div>

    <section class="card progress-card">
        <div class="wizard-progress">
            <button class="wizard-step active" type="button" data-step-target="1">
                <span>1</span>
                <strong>Tipo de cliente</strong>
            </button>

            <button class="wizard-step" type="button" data-step-target="2">
                <span>2</span>
                <strong>Documentación</strong>
            </button>

            <button class="wizard-step" type="button" data-step-target="3">
                <span>3</span>
                <strong>Tarifas</strong>
            </button>

            <button class="wizard-step" type="button" data-step-target="4">
                <span>4</span>
                <strong>Convenio</strong>
            </button>
        </div>
    </section>

    {{-- PASO 1 --}}
    <section class="card wizard-panel active" data-step="1">
        <h3 class="section-title">Paso 1 · Selecciona el tipo de cliente</h3>
        <p class="subtitle">Elige el tipo de cliente para mostrar la documentación correspondiente.</p>

        <div class="client-type-grid">
            <button class="client-type-card" type="button" data-client-type="fisica">
                <div class="client-type-icon">👤</div>
                <strong>Persona física</strong>
                <small>Cliente individual con identificación, licencia y datos personales.</small>
            </button>

            <button class="client-type-card" type="button" data-client-type="moral">
                <div class="client-type-icon">🏢</div>
                <strong>Persona moral</strong>
                <small>Empresa con razón social, RFC y representante legal.</small>
            </button>

            <button class="client-type-card" type="button" data-client-type="general">
                <div class="client-type-icon">🌐</div>
                <strong>Público general</strong>
                <small>Registro básico para cliente sin convenio preferencial completo.</small>
            </button>
        </div>

        <div class="bar">
            <button class="btn primary" id="btnGoDocs" type="button" disabled>
                Continuar a documentación
            </button>
        </div>
    </section>

    {{-- PASO 2 --}}
    <section class="card wizard-panel" data-step="2">
        <h3 class="section-title">Paso 2 · Documentación e información</h3>
        <p class="subtitle" id="docSubtitle">Completa la información y documentación correspondiente.</p>

        {{-- Persona física --}}
        <div class="doc-form doc-form-fisica">
            <h3 class="section-title mini-title">Datos personales</h3>

            <div class="grid-3">
                <div class="field">
                    <label>Nombre completo</label>
                    <input class="input" id="fisicaNombre" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="fisicaNacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input class="input" id="fisicaTelefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input class="input" id="fisicaCorreo" type="email" required>
                </div>

                <div class="field">
                    <label>Correo de factura</label>
                    <input class="input" id="fisicaCorreoFactura" type="email">
                </div>

                <div class="field">
                    <label>Número de empresa / marca</label>
                    <input class="input" id="fisicaNumeroEmpresa" type="text">
                </div>

                <div class="field">
                    <label>Nombre de empresa / marca</label>
                    <input class="input" id="fisicaNombreEmpresa" type="text">
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="fisicaNumeroIdentificacion" type="text" placeholder="INE, pasaporte o cédula" autocomplete="off" required>
                    <small class="id-detect-helper" id="fisicaTipoIdentificacionTexto">Tipo detectado: —</small>
                    <input type="hidden" id="fisicaTipoIdentificacion" value="">
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="fisicaLicencia" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia de licencia</label>
                    <input class="input license-expiry-picker" id="fisicaVigenciaLicencia" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Datos de facturación</h3>

            <div class="grid-3">
                <div class="field">
                    <label>RFC</label>
                    <input class="input" id="fisicaFacturacionRfc" type="text">
                </div>

                <div class="field">
                    <label>Razón social</label>
                    <input class="input" id="fisicaFacturacionRazon" type="text">
                </div>

                <div class="field">
                    <label>Uso CFDI</label>
                    <select class="select" id="fisicaFacturacionCfdi">
                        <option value="">Selecciona una opción</option>
                        <option value="G03">G03 - Gastos en general</option>
                        <option value="P01">P01 - Por definir</option>
                    </select>
                </div>

                <div class="field">
                    <label>Régimen fiscal</label>
                    <input class="input" id="fisicaFacturacionRegimen" type="text">
                </div>

                <div class="field">
                    <label>País</label>
                    <select class="select" id="fisicaFiscalPais">
                        <option value="">Selecciona una opción</option>
                        <option value="México">México</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Canadá">Canadá</option>
                    </select>
                </div>

                <div class="field">
                    <label>Código postal</label>
                    <input class="input" id="fisicaFiscalCp" type="text">
                </div>

                <div class="field">
                    <label>Delegación / Municipio</label>
                    <input class="input" id="fisicaFiscalMunicipio" type="text">
                </div>

                <div class="field">
                    <label>Localidad</label>
                    <input class="input" id="fisicaFiscalLocalidad" type="text">
                </div>

                <div class="field">
                    <label>Estado</label>
                    <input class="input" id="fisicaFiscalEstado" type="text">
                </div>

                <div class="field">
                    <label>Colonia</label>
                    <input class="input" id="fisicaFiscalColonia" type="text">
                </div>

                <div class="field">
                    <label>Calle</label>
                    <input class="input" id="fisicaFiscalCalle" type="text">
                </div>

                <div class="field">
                    <label>Número exterior</label>
                    <input class="input" id="fisicaFiscalNumeroExterior" type="text">
                </div>

                <div class="field">
                    <label>Número interior</label>
                    <input class="input" id="fisicaFiscalNumeroInterior" type="text">
                </div>

                <div class="field">
                    <label>Referencias</label>
                    <textarea class="txta" id="fisicaFiscalReferencias" placeholder="Entre calles, fachada, indicaciones..."></textarea>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="fisicaIdentificacionFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="fisicaIdentificacionTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="fisicaLicenciaFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="fisicaLicenciaTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Constancia de Situación Fiscal</label>
                    <input class="input document-file-input" id="fisicaCsf" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>
            </div>
        </div>

        {{-- Persona moral --}}
        <div class="doc-form doc-form-moral">
            <h3 class="section-title mini-title">Datos de la empresa</h3>

            <div class="grid-3">
                <div class="field company-search-field">
                    <label>Razón social</label>

                    <div class="company-search-wrap">
                        <input class="input" id="moralRazon" type="text" placeholder="Buscar empresa..." autocomplete="off" required>
                        <button class="btn ghost company-search-btn" id="btnBuscarEmpresa" type="button">Buscar</button>
                    </div>

                    <div class="company-results" id="companyResults">
                        <div class="company-empty">Escribe una razón social para buscar.</div>
                    </div>
                </div>

                <div class="field">
                    <label>Teléfono empresa</label>
                    <input class="input" id="moralTelefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo empresa</label>
                    <input class="input" id="moralCorreo" type="email" required>
                </div>
            </div>

            <h3 class="section-title mini-title">Representante legal</h3>

            <div class="grid-3">
                <div class="field">
                    <label>Nombre representante legal</label>
                    <input class="input" id="moralRepresentante" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="moralRepresentanteNacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono representante legal</label>
                    <input class="input" id="moralTelefonoRepresentante" type="text" required>
                </div>

                <div class="field">
                    <label>Correo representante legal</label>
                    <input class="input" id="moralCorreoRepresentante" type="email" required>
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="moralRepresentanteIdentificacion" type="text" required>
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="moralLicenciaTitular" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia licencia</label>
                    <input class="input license-expiry-picker" id="moralVigenciaLicenciaTitular" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Datos de facturación</h3>

            <div class="grid-3">
                <div class="field">
                    <label>RFC</label>
                    <input class="input" id="moralFacturacionRfc" type="text" required>
                </div>

                <div class="field">
                    <label>Razón social fiscal</label>
                    <input class="input" id="moralFacturacionRazon" type="text" required>
                </div>

                <div class="field">
                    <label>Correo de facturación</label>
                    <input class="input" id="moralFacturacionCorreo" type="email" required>
                </div>

                <div class="field">
                    <label>Uso CFDI</label>
                    <select class="select" id="moralFacturacionCfdi" required>
                        <option value="">Selecciona una opción</option>
                        <option value="G03">G03 - Gastos en general</option>
                        <option value="P01">P01 - Por definir</option>
                    </select>
                </div>

                <div class="field">
                    <label>Régimen fiscal</label>
                    <input class="input" id="moralFacturacionRegimen" type="text" required>
                </div>

                <div class="field">
                    <label>País</label>
                    <select class="select" id="moralFiscalPais" required>
                        <option value="">Selecciona una opción</option>
                        <option value="México">México</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Canadá">Canadá</option>
                    </select>
                </div>

                <div class="field">
                    <label>Código postal</label>
                    <input class="input" id="moralFiscalCp" type="text" required>
                </div>

                <div class="field">
                    <label>Delegación / Municipio</label>
                    <input class="input" id="moralFiscalMunicipio" type="text" required>
                </div>

                <div class="field">
                    <label>Localidad</label>
                    <input class="input" id="moralFiscalLocalidad" type="text" required>
                </div>

                <div class="field">
                    <label>Estado</label>
                    <input class="input" id="moralFiscalEstado" type="text" required>
                </div>

                <div class="field">
                    <label>Colonia</label>
                    <input class="input" id="moralFiscalColonia" type="text" required>
                </div>

                <div class="field">
                    <label>Calle</label>
                    <input class="input" id="moralFiscalCalle" type="text" required>
                </div>

                <div class="field">
                    <label>Número exterior</label>
                    <input class="input" id="moralFiscalNumeroExterior" type="text" required>
                </div>

                <div class="field">
                    <label>Número interior</label>
                    <input class="input" id="moralFiscalNumeroInterior" type="text">
                </div>

                <div class="field">
                    <label>Referencias</label>
                    <textarea class="txta" id="moralFiscalReferencias" placeholder="Entre calles, fachada, indicaciones..."></textarea>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Constancia de Situación Fiscal</label>
                    <input class="input document-file-input" id="moralCsf" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Acta constitutiva</label>
                    <input class="input document-file-input" id="moralActaConstitutiva" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="moralIdentificacionFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="moralIdentificacionTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="moralLicenciaFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="moralLicenciaTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Responsiva Cliente</label>
                    <input class="input document-file-input" id="moralResponsivaCliente" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>
            </div>

            <h3 class="section-title mini-title">Conductores adicionales</h3>

            <div class="additional-driver-box">
                <div class="grid-3">
                    <div class="field">
                        <label>Nombre conductor</label>
                        <input class="input" id="driverNombre" type="text">
                    </div>

                    <div class="field">
                        <label>Fecha de nacimiento</label>
                        <input class="input birthdate-picker" id="driverNacimiento" type="text" placeholder="dd-mmm-aaaa" readonly>
                    </div>

                    <div class="field">
                        <label>Teléfono conductor</label>
                        <input class="input" id="driverTelefono" type="text">
                    </div>

                    <div class="field">
                        <label>Correo conductor</label>
                        <input class="input" id="driverCorreo" type="email">
                    </div>

                    <div class="field">
                        <label>No. identificación</label>
                        <input class="input" id="driverIne" type="text">
                    </div>

                    <div class="field">
                        <label>No. licencia</label>
                        <input class="input" id="driverLicencia" type="text">
                    </div>

                    <div class="field">
                        <label>Vigencia licencia</label>
                        <input class="input license-expiry-picker" id="driverVigenciaLicencia" type="text" placeholder="dd-mmm-aaaa" readonly>
                    </div>

                    <div class="field document-upload-field">
                        <label>Identificación frontal</label>
                        <input class="input document-file-input" id="driverIdentificacionFrontal" type="file" accept="image/*,.pdf" capture="environment">
                    </div>

                    <div class="field document-upload-field">
                        <label>Identificación trasera</label>
                        <input class="input document-file-input" id="driverIdentificacionTrasera" type="file" accept="image/*,.pdf" capture="environment">
                    </div>

                    <div class="field document-upload-field">
                        <label>Licencia frontal</label>
                        <input class="input document-file-input" id="driverFotoLicencia" type="file" accept="image/*,.pdf" capture="environment">
                    </div>

                    <div class="field document-upload-field">
                        <label>Licencia trasera</label>
                        <input class="input document-file-input" id="driverLicenciaTrasera" type="file" accept="image/*,.pdf" capture="environment">
                    </div>

                    <div class="field signature-field">
                        <label>Firma del conductor</label>

                        <div class="signature-preview-box" data-signature-target="driverFirma">
                            <input type="hidden" id="driverFirma" class="signature-value">

                            <div class="signature-preview-empty">Sin firma registrada</div>
                            <img class="signature-preview-img" alt="Firma del conductor">

                            <button
                                class="btn primary btn-open-signature-modal"
                                type="button"
                                data-signature-target="driverFirma"
                                data-signature-title="Firma del conductor"
                            >
                                Firmar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bar">
                    <button class="btn primary" id="btnAddDriver" type="button">
                        Agregar conductor
                    </button>
                </div>
            </div>

            <div class="drivers-list" id="driversList">
                <div class="empty-clauses">Aún no hay conductores adicionales.</div>
            </div>
        </div>

        {{-- Público general --}}
        <div class="doc-form doc-form-general">
            <div class="grid-3">
                <div class="field">
                    <label>Nombre completo</label>
                    <input class="input" id="generalNombre" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="generalNacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input class="input" id="generalTelefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input class="input" id="generalCorreo" type="email" required>
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="generalIdentificacion" type="text" required>
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="generalLicencia" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia de licencia</label>
                    <input class="input license-expiry-picker" id="generalVigenciaLicencia" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="generalIdentificacionFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="generalIdentificacionTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="generalLicenciaFrontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="generalLicenciaTrasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>
            </div>
        </div>

        <div class="bar">
            <button class="btn ghost" type="button" data-prev-step="1">Regresar</button>
            <button class="btn primary" type="button" data-next-step="3">Continuar a tarifas</button>
        </div>
    </section>

    {{-- PASO 3 --}}
    <section class="card wizard-panel" data-step="3">
        <h3 class="section-title">Paso 3 · Tarifas por categoría</h3>
        <p class="subtitle">Captura la tarifa diaria y cotiza los paquetes de protección.</p>

        <div class="rate-table-wrap">
            <table class="rate-table protection-rate-table">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Tarifa diaria</th>
                        <th>Tarifa semanal</th>
                        <th>Tarifa mensual</th>
                        <th>Protección</th>
                        <th>Total diario</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($categorias as $index => $categoria)
                        <tr data-rate-row="{{ $index }}">
                            <td>{{ $categoria }}</td>

                            <td>
                                <input class="input money-input rate-daily-input" type="text" placeholder="$0.00" data-row-index="{{ $index }}">
                            </td>

                            <td>
                                <input class="input money-input" type="text" placeholder="$0.00">
                            </td>

                            <td>
                                <input class="input money-input" type="text" placeholder="$0.00">
                            </td>

                            <td>
                                <div class="selected-protection" id="selectedProtection{{ $index }}">
                                    Sin protección
                                </div>
                            </td>

                            <td>
                                <div class="final-daily-price" id="finalDailyPrice{{ $index }}">
                                    $0.00 MXN
                                </div>
                            </td>

                            <td>
                                <button class="btn ghost btn-open-protections" type="button" data-row-index="{{ $index }}" data-category="{{ $categoria }}">
                                    Ver protecciones
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bar">
            <button class="btn ghost" type="button" data-prev-step="2">Regresar</button>
            <button class="btn primary" type="button" data-next-step="4">Continuar a convenio</button>
        </div>
    </section>

    {{-- MODAL PROTECCIONES --}}
<div class="protections-modal" id="protectionsModal">
    <div class="protections-modal-card">
        <div class="protections-modal-header">
            <div>
                <h3>Protecciones</h3>
                <p id="protectionModalCategory">Activa una protección para calcular el total.</p>
            </div>

            <div class="protection-summary-bubbles">
                <div class="summary-bubble">
                    <small>Tarifa diaria</small>
                    <strong id="modalBasePrice">$0.00 MXN</strong>
                </div>

                <div class="summary-bubble">
                    <small>Protección</small>
                    <strong id="modalProtectionPrice">$0.00 MXN</strong>
                </div>

                <div class="summary-bubble total">
                    <small>Total seleccionado</small>
                    <strong id="modalSelectedTotal">$0.00 MXN</strong>
                </div>

                <button class="modal-close-btn" id="btnCloseProtections" type="button">×</button>
            </div>
        </div>

        {{-- Este contenedor se llenará dinámicamente con JS --}}
        <div class="protections-modal-body" id="protectionPacksTrack">
            <div class="loading">Cargando paquetes...</div>
        </div>

        <div class="protections-modal-footer">
            <button class="btn ghost" id="btnCancelProtections" type="button">Cerrar</button>
            <button class="btn primary" id="btnApplyProtection" type="button">Aplicar protección</button>
        </div>
    </div>
</div>
    {{-- PASO 4 --}}
    <section class="card wizard-panel" data-step="4">
        <h3 class="section-title">Paso 4 · Convenio y firmas</h3>
        <p class="subtitle" id="agreementSubtitle">
            Genera el convenio y captura las firmas correspondientes.
        </p>

        <div class="agreement-box">
            <div class="agreement-info">
                <h4 id="agreementTitle">Convenio Member Prefer</h4>
                <p id="agreementDescription">
                    El convenio se generará según el tipo de cliente seleccionado.
                </p>
            </div>

            <div class="agreement-actions">
                <button class="btn ghost" id="btnViewPdf" type="button">Generar convenio PDF</button>
                <button class="btn primary" id="btnShowClause" type="button">Agregar cláusula</button>
            </div>
        </div>

        <div class="clause-panel" id="clausePanel">
            <div class="field">
                <label>Nueva cláusula</label>
                <textarea class="txta" id="clauseText" placeholder="Escribe aquí la cláusula adicional..."></textarea>
            </div>

            <div class="bar">
                <button class="btn ghost" id="btnCancelClause" type="button">Cancelar</button>
                <button class="btn primary" id="btnAddClause" type="button">Guardar cláusula</button>
            </div>
        </div>

        <div class="clauses-list-wrap">
            <h3 class="section-title" id="clausesTitle">Cláusulas del convenio</h3>

            <div class="clauses-list" id="clausesList">
                <div class="empty-clauses">Aún no hay cláusulas agregadas.</div>
            </div>
        </div>

        {{-- PERSONA FÍSICA --}}
        <div class="agreement-mode agreement-mode-fisica">
            <h3 class="section-title mini-title">Firmas del convenio · Persona física</h3>

            <div class="grid-2">
                <div class="field signature-field">
                    <label>Firma cliente / nombre completo</label>

                    <div class="signature-preview-box" data-signature-target="firmaUsuarioFisica">
                        <input type="hidden" id="firmaUsuarioFisica" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma cliente persona física">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaUsuarioFisica"
                            data-signature-title="Firma cliente / nombre completo">
                            Firmar
                        </button>
                    </div>
                </div>

                <div class="field signature-field">
                    <label>Firma asesor Viajero</label>

                    <div class="signature-preview-box" data-signature-target="firmaAsesorFisica">
                        <input type="hidden" id="firmaAsesorFisica" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma asesor Viajero">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaAsesorFisica"
                            data-signature-title="Firma asesor Viajero">
                            Firmar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PERSONA MORAL --}}
        <div class="agreement-mode agreement-mode-moral">
            <h3 class="section-title mini-title">Firmas del convenio · Persona moral</h3>

            <div class="grid-3">
                <div class="field signature-field">
                    <label>Firma representante legal</label>

                    <div class="signature-preview-box" data-signature-target="firmaRepresentanteLegal">
                        <input type="hidden" id="firmaRepresentanteLegal" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma representante legal">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaRepresentanteLegal"
                            data-signature-title="Firma representante legal">
                            Firmar
                        </button>
                    </div>
                </div>

                <div class="field signature-field">
                    <label>Firma conductor adicional</label>

                    <div class="signature-preview-box" data-signature-target="firmaConductorConvenio">
                        <input type="hidden" id="firmaConductorConvenio" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma conductor adicional">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaConductorConvenio"
                            data-signature-title="Firma conductor adicional">
                            Firmar
                        </button>
                    </div>
                </div>

                <div class="field signature-field">
                    <label>Firma asesor Viajero</label>

                    <div class="signature-preview-box" data-signature-target="firmaAsesorMoral">
                        <input type="hidden" id="firmaAsesorMoral" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma asesor Viajero">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaAsesorMoral"
                            data-signature-title="Firma asesor Viajero">
                            Firmar
                        </button>
                    </div>
                </div>
            </div>

            <h3 class="section-title mini-title">Responsivas individuales</h3>

            <div class="agreement-box">
                <div class="agreement-info">
                    <h4>Responsiva por conductor</h4>
                    <p>Se generará una responsiva independiente por cada conductor adicional registrado.</p>
                </div>

                <div class="agreement-actions">
                    <button class="btn ghost" id="btnGenerateResponsivas" type="button">
                        Generar responsivas
                    </button>
                </div>
            </div>

            <div class="responsivas-list" id="responsivasList">
                <div class="empty-clauses">Aún no hay responsivas generadas.</div>
            </div>
        </div>

        {{-- PÚBLICO GENERAL --}}
        <div class="agreement-mode agreement-mode-general">
            <h3 class="section-title mini-title">Firmas del convenio · Público general</h3>

            <div class="grid-2">
                <div class="field signature-field">
                    <label>Firma usuario</label>

                    <div class="signature-preview-box" data-signature-target="firmaUsuarioGeneral">
                        <input type="hidden" id="firmaUsuarioGeneral" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma usuario">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaUsuarioGeneral"
                            data-signature-title="Firma usuario">
                            Firmar
                        </button>
                    </div>
                </div>

                <div class="field signature-field">
                    <label>Firma asesor Viajero</label>

                    <div class="signature-preview-box" data-signature-target="firmaAsesorGeneral">
                        <input type="hidden" id="firmaAsesorGeneral" class="signature-value">

                        <div class="signature-preview-empty">Sin firma registrada</div>
                        <img class="signature-preview-img" alt="Firma asesor Viajero">

                        <button class="btn primary btn-open-signature-modal" type="button"
                            data-signature-target="firmaAsesorGeneral"
                            data-signature-title="Firma asesor Viajero">
                            Firmar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="field mini-title">
            <label>Convenio firmado</label>
            <input class="input document-file-input" id="convenioFirmado" type="file" accept="image/*,.pdf">
        </div>

        <div class="field">
            <label>Observaciones</label>
            <textarea class="txta" id="observacionesConvenio" placeholder="Observaciones del convenio..."></textarea>
        </div>

        <div class="bar">
            <button class="btn ghost" type="button" data-prev-step="3">Regresar</button>
            <button class="btn primary" id="btnFinishVisual" type="button">Finalizar registro</button>
        </div>
    </section>

    {{-- MODAL FIRMA ELECTRÓNICA --}}
    <div class="signature-modal" id="signatureModal">
        <div class="signature-modal-card">
            <div class="signature-modal-header">
                <div>
                    <h3 id="signatureModalTitle">Firma electrónica</h3>
                    <p>Dibuja la firma dentro del recuadro.</p>
                </div>

                <button class="modal-close-btn" id="btnCloseSignatureModal" type="button">×</button>
            </div>

            <div class="signature-modal-body">
                <canvas class="signature-modal-canvas" id="signatureModalCanvas"></canvas>
            </div>

            <div class="signature-modal-footer">
                <button class="btn ghost" id="btnClearSignatureModal" type="button">
                    Limpiar firma
                </button>

                <button class="btn primary" id="btnSaveSignatureModal" type="button">
                    Guardar firma
                </button>
            </div>
        </div>
    </div>

    <div id="toast"></div>

</main>
@endsection

@section('js-vistaAltaCliente')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="{{ asset('js/AltaCliente.js') }}" defer></script>
@endsection
