@extends('layouts.Usuarios')

@section('Titulo', __('messages.mi_perfil'))

{{-- 🎨 Estilos específicos de la vista --}}
@section('css-vistaPerfil')
<link rel="stylesheet" href="{{ asset('css/Perfil.css') }}">
@endsection

{{-- 🧱 Contenido principal --}}
@section('contenidoPerfil')
<main class="page">
  <section class="hero">
    <div class="hero-bg">
      <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?q=80&w=1600&auto=format&fit=crop" alt="">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>{{ __('messages.mi_perfil_titulo') }} <span>{{ __('messages.perfil') }}</span></h1>
      <p>{{ __('messages.administra_info') }}</p>
    </div>
  </section>

  <section class="dash">
    <aside class="card pad side">
      <h3><i class="fa-regular fa-circle-user" style="color:var(--brand)"></i> {{ __('messages.cuenta') }}</h3>
      <div class="s-menu" id="sMenu">
        <button class="s-btn active" data-target="#pPerfil"><i class="fa-solid fa-id-card"></i> {{ __('messages.mi_perfil_menu') }}</button>
        <button class="s-btn" data-target="#pReservas"><i class="fa-solid fa-calendar-check"></i> {{ __('messages.mis_reservaciones') }}</button>
        <button class="s-btn" data-target="#pTarjetas"><i class="fa-solid fa-credit-card"></i> {{ __('messages.mis_tarjetas') }}</button>
      </div>
      <button class="logout" id="btnLogout"><i class="fa-solid fa-right-from-bracket"></i> {{ __('messages.cerrar_sesion') }}</button>
    </aside>

    <div class="stack">
      {{-- Panel: Mi perfil --}}
      <section class="card pad panel show" id="pPerfil">
        <h3>{{ __('messages.mi_perfil_titulo') }}</h3>
        <p class="subtitle">{{ __('messages.manten_datos') }}</p>

        <form id="formPerfil">
          <div class="grid2">
            <div class="field">
              <label>{{ __('messages.nombre_completo') }}</label>
              <input id="pfName" placeholder="{{ __('messages.nombre_placeholder') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('messages.correo') }}</label>
              <input id="pfEmail" type="email" placeholder="{{ __('messages.email_placeholder') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('messages.telefono') }}</label>
              <input id="pfPhone" type="tel" placeholder="{{ __('messages.telefono_placeholder') }}" disabled>
            </div>

            <div class="field">
              <label>{{ __('messages.fecha_nacimiento') }}</label>
              <div class="nice-date disabled" id="birthPicker">
                <i class="fa-regular fa-calendar"></i>
                <input id="pfBirthDisplay" type="text" placeholder="{{ __('messages.fecha_placeholder') }}" readonly>
                <div class="cal-pop" aria-hidden="true"></div>
              </div>
              <input id="pfBirth" type="hidden">
            </div>

            <div class="field">
              <label>{{ __('messages.edad') }}</label>
              <input id="pfAge" placeholder="—" disabled>
            </div>
            <div class="field">
              <label>{{ __('messages.ubicacion') }}</label>
              <input id="pfLoc" placeholder="{{ __('messages.ubicacion_placeholder') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('messages.no_membresia') }}</label>
              <input id="pfMember" placeholder="{{ __('messages.membresia_placeholder') }}" disabled>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-ghost" id="btnEdit">
              <i class="fa-regular fa-pen-to-square"></i> {{ __('messages.editar') }}
            </button>
            <button type="submit" class="btn btn-primary" id="btnSave" disabled>
              <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.guardar') }}
            </button>
          </div>
        </form>
      </section>

      {{-- Panel: Reservas --}}
      <section class="card pad panel" id="pReservas">
        <h3>{{ __('messages.mis_reservaciones') }}</h3>
        <p class="subtitle">{{ __('messages.consulta_historial') }}</p>

        <div class="filters" id="resFilters">
          <button class="pill active" data-status="all">{{ __('messages.todas') }}</button>
          <button class="pill" data-status="pendiente">{{ __('messages.pendientes') }}</button>
          <button class="pill" data-status="activa">{{ __('messages.activas') }}</button>
          <button class="pill" data-status="finalizada">{{ __('messages.finalizadas') }}</button>
        </div>

        <div class="table-wrap">
          <table class="table" id="resTable" aria-label="{{ __('messages.mis_reservaciones') }}">
            <thead>
              <tr>
                <th>{{ __('messages.contrato') }}</th><th>{{ __('messages.auto') }}</th><th>{{ __('messages.fechas') }}</th><th>{{ __('messages.estatus') }}</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </section>

      {{-- Panel: Tarjetas --}}
      <section class="card pad panel" id="pTarjetas">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h3>{{ __('messages.mis_tarjetas') }}</h3>
            <p class="subtitle">{{ __('messages.administra_tarjetas') }}</p>
          </div>
          <button class="btn btn-primary" id="btnAddCard">
            <i class="fa-solid fa-plus"></i> {{ __('messages.agregar_tarjeta') }}
          </button>
        </div>

        <div class="cards" id="cardsWrap"></div>
      </section>
    </div>
  </section>
</main>

{{-- 🔹 Modal: Agregar / Editar Tarjeta --}}
<div class="modal" id="cardModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true">
    <button class="modal-close" id="cardClose" aria-label="{{ __('messages.cerrar') }}"><i class="fa-regular fa-circle-xmark"></i></button>
    <h3 class="modal-title" id="cardModalTitle"><i class="fa-solid fa-credit-card"></i> {{ __('messages.metodo_pago') }}</h3>
    <form id="cardForm">
      <div class="field">
        <label>{{ __('messages.nombre_tarjeta') }}</label>
        <input id="ccName" required placeholder="{{ __('messages.nombre_tarjeta_placeholder') }}">
      </div>
      <div class="grid2">
        <div class="field">
          <label>{{ __('messages.numero_tarjeta') }}</label>
          <input id="ccNumber" inputmode="numeric" maxlength="19" required placeholder="{{ __('messages.numero_placeholder') }}">
        </div>
        <div class="field">
          <label>{{ __('messages.marca') }}</label>
          <select id="ccBrand" required>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
          </select>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label>{{ __('messages.expiracion') }}</label>
          <input id="ccExp" placeholder="{{ __('messages.expiracion_placeholder') }}" maxlength="5" required>
        </div>
        <div class="field">
          <label>{{ __('messages.alias') }}</label>
          <input id="ccAlias" placeholder="{{ __('messages.alias_placeholder') }}">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" id="cardCancel">{{ __('messages.cancelar') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('messages.guardar') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- 🔹 Modal: Cerrar Sesión --}}
<div class="modal" id="logoutModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="loTitle" aria-describedby="loDesc">
    <button class="modal-close" id="loClose" aria-label="{{ __('messages.cerrar') }}"><i class="fa-regular fa-circle-xmark"></i></button>
    <div class="logout-card-head">
      <div class="lo-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
      <div>
        <h3 class="lo-title" id="loTitle">{{ __('messages.cerrar_sesion_pregunta') }}</h3>
        <p class="lo-sub" id="loDesc">{{ __('messages.sesion_cerrara') }}</p>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-light" id="logoutCancel"><i class="fa-regular fa-circle-xmark"></i> {{ __('messages.cancelar') }}</button>
      <button class="btn btn-danger" id="logoutConfirm"><i class="fa-solid fa-right-from-bracket"></i> {{ __('messages.cerrar_sesion') }}</button>
    </div>
  </div>
</div>

{{-- 🔹 Toast de confirmación --}}
<div class="toast" id="toast"><i class="fa-solid fa-check"></i> {{ __('messages.cambios_guardados') }}</div>
@endsection

{{-- 🧠 Scripts específicos --}}
@section('js-vistaPerfil')
<script src="{{ asset('js/Perfil.js') }}"></script>
@endsection
