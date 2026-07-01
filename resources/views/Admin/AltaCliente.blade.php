@extends('layouts.Ventas')

@section('Titulo', 'Alta Member Prefer')

@section('css-vistaAltaCliente')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/AltaCliente.css') }}">
@endsection

@section('contenidoAltaCliente')
@php
    // Fallback por si el controlador no envía datos (evita que truene la vista)
    $categorias   = $categorias   ?? collect();
    $protecciones = $protecciones ?? collect();
@endphp

{{-- Mensajes de resultado --}}
@if(session('success'))
    <div class="alert-flash alert-success" style="margin:12px 0;padding:12px 16px;border-radius:10px;background:#dcfce7;color:#166534;font-weight:600;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-flash alert-error" style="margin:12px 0;padding:12px 16px;border-radius:10px;background:#fee2e2;color:#991b1b;font-weight:600;">
        {{ session('error') }}
    </div>
@endif

{{-- ===== FORM PRINCIPAL: envuelve los 4 pasos ===== --}}
<form id="altaClienteForm"
      method="POST"
      action="{{ route('altaCliente.store') }}"
      enctype="multipart/form-data">
    @csrf

    {{-- Tipo de cliente: lo llena el JS al seleccionar tarjeta (campo hidden) --}}
    <input type="hidden" name="tipo_persona" id="tipoPersonaInput" value="">

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
                    <input class="input" id="fisicaNombre" name="fisica_nombre" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="fisicaNacimiento" name="fisica_nacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input class="input" id="fisicaTelefono" name="fisica_telefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input class="input" id="fisicaCorreo" name="fisica_correo" type="email" required>
                </div>

                <div class="field">
                    <label>Correo de factura</label>
                    <input class="input" id="fisicaCorreoFactura" name="fisica_correo_factura" type="email">
                </div>

                <div class="field">
                    <label>Número de empresa / marca</label>
                    <input class="input" id="fisicaNumeroEmpresa" name="fisica_numero_empresa" type="text">
                </div>

                <div class="field">
                    <label>Nombre de empresa / marca</label>
                    <input class="input" id="fisicaNombreEmpresa" name="fisica_nombre_empresa" type="text">
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="fisicaNumeroIdentificacion" name="fisica_numero_identificacion" type="text" placeholder="INE, pasaporte o cédula" autocomplete="off" required>
                    <small class="id-detect-helper" id="fisicaTipoIdentificacionTexto">Tipo detectado: —</small>
                    <input type="hidden" id="fisicaTipoIdentificacion" name="fisica_tipo_identificacion" value="">
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="fisicaLicencia" name="fisica_licencia" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia de licencia</label>
                    <input class="input license-expiry-picker" id="fisicaVigenciaLicencia" name="fisica_vigencia_licencia" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Datos de facturación</h3>

            <div class="grid-3">
                <div class="field">
                    <label>RFC</label>
                    <input class="input" id="fisicaFacturacionRfc" name="fisica_facturacion_rfc" type="text">
                </div>

                <div class="field">
                    <label>Razón social</label>
                    <input class="input" id="fisicaFacturacionRazon" name="fisica_facturacion_razon" type="text">
                </div>

                <div class="field">
                    <label>Uso CFDI</label>
                    <select class="select" id="fisicaFacturacionCfdi" name="fisica_facturacion_cfdi">
                        <option value="">Selecciona una opción</option>
                        <option value="G03">G03 - Gastos en general</option>
                        <option value="P01">P01 - Por definir</option>
                    </select>
                </div>

                <div class="field">
                    <label>Régimen fiscal</label>
                    <input class="input" id="fisicaFacturacionRegimen" name="fisica_facturacion_regimen" type="text">
                </div>

                <div class="field">
                    <label>País</label>
                    <select class="select" id="fisicaFiscalPais" name="fisica_fiscal_pais">
                        <option value="">Selecciona una opción</option>
                        <option value="México">México</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Canadá">Canadá</option>
                    </select>
                </div>

                <div class="field">
                    <label>Código postal</label>
                    <input class="input" id="fisicaFiscalCp" name="fisica_fiscal_cp" type="text">
                </div>

                <div class="field">
                    <label>Delegación / Municipio</label>
                    <input class="input" id="fisicaFiscalMunicipio" name="fisica_fiscal_municipio" type="text">
                </div>

                <div class="field">
                    <label>Localidad</label>
                    <input class="input" id="fisicaFiscalLocalidad" name="fisica_fiscal_localidad" type="text">
                </div>

                <div class="field">
                    <label>Estado</label>
                    <input class="input" id="fisicaFiscalEstado" name="fisica_fiscal_estado" type="text">
                </div>

                <div class="field">
                    <label>Colonia</label>
                    <input class="input" id="fisicaFiscalColonia" name="fisica_fiscal_colonia" type="text">
                </div>

                <div class="field">
                    <label>Calle</label>
                    <input class="input" id="fisicaFiscalCalle" name="fisica_fiscal_calle" type="text">
                </div>

                <div class="field">
                    <label>Número exterior</label>
                    <input class="input" id="fisicaFiscalNumeroExterior" name="fisica_fiscal_numero_exterior" type="text">
                </div>

                <div class="field">
                    <label>Número interior</label>
                    <input class="input" id="fisicaFiscalNumeroInterior" name="fisica_fiscal_numero_interior" type="text">
                </div>

                <div class="field">
                    <label>Referencias</label>
                    <textarea class="txta" id="fisicaFiscalReferencias" name="fisica_fiscal_referencias" placeholder="Entre calles, fachada, indicaciones..."></textarea>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="fisicaIdentificacionFrontal" name="fisica_identificacion_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="fisicaIdentificacionTrasera" name="fisica_identificacion_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="fisicaLicenciaFrontal" name="fisica_licencia_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="fisicaLicenciaTrasera" name="fisica_licencia_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Constancia de Situación Fiscal</label>
                    <input class="input document-file-input" id="fisicaCsf" name="fisica_csf" type="file" accept="image/*,.pdf" capture="environment" required>
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
                        <input class="input" id="moralRazon" name="moral_razon" type="text" placeholder="Buscar empresa..." autocomplete="off" required>
                        <button class="btn ghost company-search-btn" id="btnBuscarEmpresa" type="button">Buscar</button>
                    </div>

                    <div class="company-results" id="companyResults">
                        <div class="company-empty">Escribe una razón social para buscar.</div>
                    </div>
                </div>

                <div class="field">
                    <label>Teléfono empresa</label>
                    <input class="input" id="moralTelefono" name="moral_telefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo empresa</label>
                    <input class="input" id="moralCorreo" name="moral_correo" type="email" required>
                </div>
            </div>

            <h3 class="section-title mini-title">Representante legal</h3>

            <div class="grid-3">
                <div class="field">
                    <label>Nombre representante legal</label>
                    <input class="input" id="moralRepresentante" name="moral_representante" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="moralRepresentanteNacimiento" name="moral_representante_nacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono representante legal</label>
                    <input class="input" id="moralTelefonoRepresentante" name="moral_telefono_representante" type="text" required>
                </div>

                <div class="field">
                    <label>Correo representante legal</label>
                    <input class="input" id="moralCorreoRepresentante" name="moral_correo_representante" type="email" required>
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="moralRepresentanteIdentificacion" name="moral_representante_identificacion" type="text" required>
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="moralLicenciaTitular" name="moral_licencia_titular" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia licencia</label>
                    <input class="input license-expiry-picker" id="moralVigenciaLicenciaTitular" name="moral_vigencia_licencia_titular" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Datos de facturación</h3>

            <div class="grid-3">
                <div class="field">
                    <label>RFC</label>
                    <input class="input" id="moralFacturacionRfc" name="moral_facturacion_rfc" type="text" required>
                </div>

                <div class="field">
                    <label>Razón social fiscal</label>
                    <input class="input" id="moralFacturacionRazon" name="moral_facturacion_razon" type="text" required>
                </div>

                <div class="field">
                    <label>Correo de facturación</label>
                    <input class="input" id="moralFacturacionCorreo" name="moral_facturacion_correo" type="email" required>
                </div>

                <div class="field">
                    <label>Uso CFDI</label>
                    <select class="select" id="moralFacturacionCfdi" name="moral_facturacion_cfdi" required>
                        <option value="">Selecciona una opción</option>
                        <option value="G03">G03 - Gastos en general</option>
                        <option value="P01">P01 - Por definir</option>
                    </select>
                </div>

                <div class="field">
                    <label>Régimen fiscal</label>
                    <input class="input" id="moralFacturacionRegimen" name="moral_facturacion_regimen" type="text" required>
                </div>

                <div class="field">
                    <label>País</label>
                    <select class="select" id="moralFiscalPais" name="moral_fiscal_pais" required>
                        <option value="">Selecciona una opción</option>
                        <option value="México">México</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Canadá">Canadá</option>
                    </select>
                </div>

                <div class="field">
                    <label>Código postal</label>
                    <input class="input" id="moralFiscalCp" name="moral_fiscal_cp" type="text" required>
                </div>

                <div class="field">
                    <label>Delegación / Municipio</label>
                    <input class="input" id="moralFiscalMunicipio" name="moral_fiscal_municipio" type="text" required>
                </div>

                <div class="field">
                    <label>Localidad</label>
                    <input class="input" id="moralFiscalLocalidad" name="moral_fiscal_localidad" type="text" required>
                </div>

                <div class="field">
                    <label>Estado</label>
                    <input class="input" id="moralFiscalEstado" name="moral_fiscal_estado" type="text" required>
                </div>

                <div class="field">
                    <label>Colonia</label>
                    <input class="input" id="moralFiscalColonia" name="moral_fiscal_colonia" type="text" required>
                </div>

                <div class="field">
                    <label>Calle</label>
                    <input class="input" id="moralFiscalCalle" name="moral_fiscal_calle" type="text" required>
                </div>

                <div class="field">
                    <label>Número exterior</label>
                    <input class="input" id="moralFiscalNumeroExterior" name="moral_fiscal_numero_exterior" type="text" required>
                </div>

                <div class="field">
                    <label>Número interior</label>
                    <input class="input" id="moralFiscalNumeroInterior" name="moral_fiscal_numero_interior" type="text">
                </div>

                <div class="field">
                    <label>Referencias</label>
                    <textarea class="txta" id="moralFiscalReferencias" name="moral_fiscal_referencias" placeholder="Entre calles, fachada, indicaciones..."></textarea>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Constancia de Situación Fiscal</label>
                    <input class="input document-file-input" id="moralCsf" name="moral_csf" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Acta constitutiva</label>
                    <input class="input document-file-input" id="moralActaConstitutiva" name="moral_acta_constitutiva" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="moralIdentificacionFrontal" name="moral_identificacion_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="moralIdentificacionTrasera" name="moral_identificacion_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="moralLicenciaFrontal" name="moral_licencia_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="moralLicenciaTrasera" name="moral_licencia_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Responsiva Cliente</label>
                    <input class="input document-file-input" id="moralResponsivaCliente" name="moral_responsiva_cliente" type="file" accept="image/*,.pdf" capture="environment" required>
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
                    <input class="input" id="generalNombre" name="general_nombre" type="text" required>
                </div>

                <div class="field">
                    <label>Fecha de nacimiento</label>
                    <input class="input birthdate-picker" id="generalNacimiento" name="general_nacimiento" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>

                <div class="field">
                    <label>Teléfono</label>
                    <input class="input" id="generalTelefono" name="general_telefono" type="text" required>
                </div>

                <div class="field">
                    <label>Correo</label>
                    <input class="input" id="generalCorreo" name="general_correo" type="email" required>
                </div>

                <div class="field">
                    <label>No. identificación</label>
                    <input class="input" id="generalIdentificacion" name="general_identificacion" type="text" required>
                </div>

                <div class="field">
                    <label>No. licencia</label>
                    <input class="input" id="generalLicencia" name="general_licencia" type="text" required>
                </div>

                <div class="field">
                    <label>Vigencia de licencia</label>
                    <input class="input license-expiry-picker" id="generalVigenciaLicencia" name="general_vigencia_licencia" type="text" placeholder="dd-mmm-aaaa" readonly required>
                </div>
            </div>

            <h3 class="section-title mini-title">Documentos requeridos</h3>

            <div class="docs-upload-grid docs-upload-grid-2">
                <div class="field document-upload-field">
                    <label>Identificación frontal</label>
                    <input class="input document-file-input" id="generalIdentificacionFrontal" name="general_identificacion_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Identificación trasera</label>
                    <input class="input document-file-input" id="generalIdentificacionTrasera" name="general_identificacion_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia frontal</label>
                    <input class="input document-file-input" id="generalLicenciaFrontal" name="general_licencia_frontal" type="file" accept="image/*,.pdf" capture="environment" required>
                </div>

                <div class="field document-upload-field">
                    <label>Licencia trasera</label>
                    <input class="input document-file-input" id="generalLicenciaTrasera" name="general_licencia_trasera" type="file" accept="image/*,.pdf" capture="environment" required>
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
                            <td>{{ $categoria->nombre }}</td>

                            {{-- id_categoria real para guardar la FK --}}
                            <input type="hidden" name="tarifa_id_categoria[]" value="{{ $categoria->id_categoria }}">

                            <td>
                                <input class="input money-input rate-daily-input"
                                       type="text"
                                       name="tarifa_diaria[]"
                                       placeholder="$0.00"
                                       data-row-index="{{ $index }}"
                                       value="{{ $categoria->precio_dia > 0 ? '$'.number_format($categoria->precio_dia, 2) : '' }}">
                            </td>

                            <td>
                                <input class="input money-input"
                                       type="text"
                                       name="tarifa_semanal[]"
                                       placeholder="$0.00"
                                       value="{{ $categoria->precio_semana > 0 ? '$'.number_format($categoria->precio_semana, 2) : '' }}">
                            </td>

                            <td>
                                <input class="input money-input"
                                       type="text"
                                       name="tarifa_mensual[]"
                                       placeholder="$0.00"
                                       value="{{ $categoria->precio_mes > 0 ? '$'.number_format($categoria->precio_mes, 2) : '' }}">
                            </td>

                            <td>
                                <div class="selected-protection" id="selectedProtection{{ $index }}">
                                    Sin protección
                                </div>
                                {{-- Estos los llena el JS-puente al enviar (paquete elegido) --}}
                                <input type="hidden" name="tarifa_id_paquete[]"      id="tarifaIdPaquete{{ $index }}"     value="">
                                <input type="hidden" name="tarifa_paquete_nombre[]"  id="tarifaPaqueteNombre{{ $index }}" value="">
                                <input type="hidden" name="tarifa_paquete_precio[]"  id="tarifaPaquetePrecio{{ $index }}" value="">
                            </td>

                            <td>
                                <div class="final-daily-price" id="finalDailyPrice{{ $index }}">
                                    $0.00 MXN
                                </div>
                                <input type="hidden" name="tarifa_total[]" id="tarifaTotal{{ $index }}" value="">
                            </td>

                            <td>
                                <button class="btn ghost btn-open-protections" type="button" data-row-index="{{ $index }}" data-category="{{ $categoria->nombre }}">
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
                        <input type="hidden" id="firmaUsuarioFisica" name="firma_usuario_fisica" class="signature-value">

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
                        <input type="hidden" id="firmaAsesorFisica" name="firma_asesor_fisica" class="signature-value">

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
                        <input type="hidden" id="firmaRepresentanteLegal" name="firma_representante_legal" class="signature-value">

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
                        <input type="hidden" id="firmaConductorConvenio" name="firma_conductor_convenio" class="signature-value">

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
                        <input type="hidden" id="firmaAsesorMoral" name="firma_asesor_moral" class="signature-value">

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
                        <input type="hidden" id="firmaUsuarioGeneral" name="firma_usuario_general" class="signature-value">

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
                        <input type="hidden" id="firmaAsesorGeneral" name="firma_asesor_general" class="signature-value">

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
            <input class="input document-file-input" id="convenioFirmado" name="convenio_firmado" type="file" accept="image/*,.pdf">
        </div>

        <div class="field">
            <label>Observaciones</label>
            <textarea class="txta" id="observacionesConvenio" name="observaciones_convenio" placeholder="Observaciones del convenio..."></textarea>
        </div>

        {{-- Contenedores donde el JS inyecta el estado visual como inputs hidden --}}
        <div id="hiddenDriversContainer"></div>
        <div id="hiddenDriverFilesContainer"></div>
        <div id="hiddenClausesContainer"></div>
        <div id="hiddenResponsivasContainer"></div>

        <div class="bar">
            <button class="btn ghost" type="button" data-prev-step="3">Regresar</button>
            <button class="btn primary" id="btnFinishVisual" type="submit">Finalizar registro</button>
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

</form>
{{-- ===== FIN FORM PRINCIPAL ===== --}}
@endsection

@section('js-vistaAltaCliente')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="{{ asset('js/AltaCliente.js') }}" defer></script>
@endsection
