@extends('layouts.Usuarios')

@section('Titulo', __('My Profile'))

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
      <h1>{{ __('My') }} <span>{{ __('profile') }}</span></h1>
      <p>{{ __('Manage your information, bookings and payment methods') }}</p>
    </div>
  </section>

  <section class="dash">
    <aside class="card pad side">
      <h3><i class="fa-regular fa-circle-user" style="color:var(--brand)"></i> {{ __('Account') }}</h3>
      <div class="s-menu" id="sMenu">
        <button class="s-btn active" data-target="#pPerfil"><i class="fa-solid fa-id-card"></i> {{ __('My profile') }}</button>
        <button class="s-btn" data-target="#pReservas"><i class="fa-solid fa-calendar-check"></i> {{ __('My bookings') }}</button>
        <button class="s-btn" data-target="#pTarjetas"><i class="fa-solid fa-credit-card"></i> {{ __('My cards') }}</button>
      </div>
      <button class="logout" id="btnLogout"><i class="fa-solid fa-right-from-bracket"></i> {{ __('Sign out') }}</button>
    </aside>

    <div class="stack">
      {{-- Panel: Mi perfil --}}
      <section class="card pad panel show" id="pPerfil">
        <h3>{{ __('My profile') }}</h3>
        <p class="subtitle">{{ __('Keep your information up to date to speed up your bookings') }}</p>

        <form id="formPerfil">
          <div class="grid2">
            <div class="field">
              <label>{{ __('Full name') }}</label>
              <input id="pfName" placeholder="{{ __('Your full name') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('Email') }}</label>
              <input id="pfEmail" type="email" placeholder="{{ __('your@email.com') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('Phone') }}</label>
              <input id="pfPhone" type="tel" placeholder="{{ '55 1234 5678' }}" disabled>
            </div>

            <div class="field">
              <label>{{ __('Date of birth') }}</label>
              <div class="nice-date disabled" id="birthPicker">
                <i class="fa-regular fa-calendar"></i>
                <input id="pfBirthDisplay" type="text" placeholder="dd/mm/yyyy" readonly>
                <div class="cal-pop" aria-hidden="true"></div>
              </div>
              <input id="pfBirth" type="hidden">
            </div>

            <div class="field">
              <label>{{ __('Age') }}</label>
              <input id="pfAge" placeholder="—" disabled>
            </div>
            <div class="field">
              <label>{{ __('Location') }}</label>
              <input id="pfLoc" placeholder="{{ __('City, State') }}" disabled>
            </div>
            <div class="field">
              <label>{{ __('Membership number') }}</label>
              <input id="pfMember" placeholder="VJ-XXXX-XXXX" disabled>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-ghost" id="btnEdit">
              <i class="fa-regular fa-pen-to-square"></i> {{ __('Edit') }}
            </button>
            <button type="submit" class="btn btn-primary" id="btnSave" disabled>
              <i class="fa-solid fa-floppy-disk"></i> {{ __('Save') }}
            </button>
          </div>
        </form>
      </section>

      {{-- Panel: Reservas --}}
      <section class="card pad panel" id="pReservas">
        <h3>{{ __('My bookings') }}</h3>
        <p class="subtitle">{{ __('View history and status of your reservations') }}</p>

        <div class="filters" id="resFilters">
          <button class="pill active" data-status="all">{{ __('All') }}</button>
          <button class="pill" data-status="pendiente">{{ __('Pending') }}</button>
          <button class="pill" data-status="activa">{{ __('Active') }}</button>
          <button class="pill" data-status="finalizada">{{ __('Completed') }}</button>
        </div>

        <div class="table-wrap">
          <table class="table" id="resTable" aria-label="Reservations">
            <thead>
              <tr>
                <th>{{ __('Contract') }}</th><th>{{ __('Vehicle') }}</th><th>{{ __('Dates') }}</th><th>{{ __('Status') }}</th>
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
            <h3>{{ __('My cards') }}</h3>
            <p class="subtitle">{{ __('Manage your saved payment methods') }}</p>
          </div>
          <button class="btn btn-primary" id="btnAddCard">
            <i class="fa-solid fa-plus"></i> {{ __('Add card') }}
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
    <button class="modal-close" id="cardClose" aria-label="{{ __('Close') }}"><i class="fa-regular fa-circle-xmark"></i></button>
    <h3 class="modal-title" id="cardModalTitle"><i class="fa-solid fa-credit-card"></i> {{ __('Payment method') }}</h3>
    <form id="cardForm">
      <div class="field">
        <label>{{ __('Name on card') }}</label>
        <input id="ccName" required placeholder="{{ __('As it appears on the card') }}">
      </div>
      <div class="grid2">
        <div class="field">
          <label>{{ __('Card number') }}</label>
          <input id="ccNumber" inputmode="numeric" maxlength="19" required placeholder="#### #### #### ####">
        </div>
        <div class="field">
          <label>{{ __('Brand') }}</label>
          <select id="ccBrand" required>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
          </select>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label>{{ __('Expiration (MM/YY)') }}</label>
          <input id="ccExp" placeholder="MM/YY" maxlength="5" required>
        </div>
        <div class="field">
          <label>{{ __('Alias (optional)') }}</label>
          <input id="ccAlias" placeholder="{{ __('e.g. Personal, Business') }}">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" id="cardCancel">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- 🔹 Modal: Cerrar Sesión --}}
<div class="modal" id="logoutModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="loTitle" aria-describedby="loDesc">
    <button class="modal-close" id="loClose" aria-label="{{ __('Close') }}"><i class="fa-regular fa-circle-xmark"></i></button>
    <div class="logout-card-head">
      <div class="lo-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
      <div>
        <h3 class="lo-title" id="loTitle">{{ __('Sign out?') }}</h3>
        <p class="lo-sub" id="loDesc">{{ __('Your session will be closed on this device.') }}</p>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-light" id="logoutCancel"><i class="fa-regular fa-circle-xmark"></i> {{ __('Cancel') }}</button>
      <button class="btn btn-danger" id="logoutConfirm"><i class="fa-solid fa-right-from-bracket"></i> {{ __('Sign out') }}</button>
    </div>
  </div>
</div>

{{-- 🔹 Toast de confirmación --}}
<div class="toast" id="toast"><i class="fa-solid fa-check"></i> {{ __('Changes saved') }}</div>
@endsection

{{-- 🧠 Scripts específicos --}}
@section('js-vistaPerfil')
<script src="{{ asset('js/Perfil.js') }}"></script>
@endsection
