@extends('layouts.Usuarios')

@section('Titulo', __('Sign in'))

@section('css-vistaLogin')
  <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
  {{-- Alertify --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css">

  {{-- Metadatos para que el JS abra el modal de verificación automáticamente --}}
  @if (session('show_modal'))
    <meta name="show-modal" content="true">
    <meta name="correo-modal" content="{{ session('correo_modal') }}">
  @endif
@endsection

@section('contenidoLogin')
<main class="page">

  {{-- HERO --}}
  <section class="hero hero-auth">
    <div class="hero-bg">
      <img src="{{ asset('img/login.webp') }}"
           alt=""
           width="1920" height="1080"
           loading="eager"
           fetchpriority="high"
           decoding="async">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>{{ __('Welcome to') }} <span>{{ __('Viajero') }}</span></h1>
      <p>{{ __('Sign in or create an account to continue') }}</p>
    </div>
  </section>

  {{-- TARJETA LOGIN / REGISTER --}}
  <section class="auth-container">
    <div class="auth-card-2col">

      {{-- Columna formulario --}}
      <div class="auth-col form-side">
        <div class="segmented" id="tabs">
          <button type="button"
                  class="seg-btn {{ old('form_type') === 'register' ? '' : 'active' }}"
                  data-target="#panel-login">{{ __('Sign in') }}</button>
          <button type="button"
                  class="seg-btn {{ old('form_type') === 'register' ? 'active' : '' }}"
                  data-target="#panel-register">{{ __('Create account') }}</button>
          <span class="seg-slider"></span>
        </div>

        {{-- Panel LOGIN --}}
        <div class="auth-panel {{ old('form_type') === 'register' ? '' : 'show' }}" id="panel-login">
          <form id="formLogin" method="POST" action="{{ route('auth.login') }}" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="login">

            <div class="field">
              <input id="loginUser" name="login" type="text" placeholder=" "
                     value="{{ old('login') }}"
                     autocomplete="username"
                     required>
              <label for="loginUser"><i class="fa-regular fa-envelope" aria-hidden="true"></i> {{ __('Email or Username') }}</label>
            </div>

            <div class="field">
              <input id="loginPass" name="password" type="password" placeholder=" "
                     autocomplete="current-password"
                     required>
              <label for="loginPass"><i class="fa-solid fa-lock" aria-hidden="true"></i> {{ __('Password') }}</label>
              <button type="button" class="eye" data-target="#loginPass" aria-label="{{ __('Show / hide password') }}">
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
              </button>
            </div>

            <div class="aux-row">
              <label class="check">
                <input type="checkbox" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>{{ __('Remember me') }}</span>
              </label>
              <button type="button" class="link" id="forgotLink">{{ __('Forgot your password?') }}</button>
            </div>

            <button class="btn-primary w100" type="submit" id="btnLogin">
              <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> {{ __('Sign in') }}
            </button>
          </form>
        </div>

        {{-- Panel REGISTER --}}
        <div class="auth-panel {{ old('form_type') === 'register' ? 'show' : '' }}" id="panel-register">
          <form id="formRegister" method="POST" action="{{ route('auth.register') }}" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="register">

            <div class="grid2">
              <div class="field">
                <input id="rName" name="nombres" type="text" placeholder=" "
                       value="{{ old('nombres') }}"
                       autocomplete="given-name"
                       required>
                <label for="rName"><i class="fa-regular fa-user" aria-hidden="true"></i> {{ __('First name') }}</label>
              </div>
              <div class="field">
                <input id="rApPat" name="ap_paterno" type="text" placeholder=" "
                       value="{{ old('ap_paterno') }}"
                       autocomplete="family-name"
                       required>
                <label for="rApPat"><i class="fa-regular fa-user" aria-hidden="true"></i> {{ __('Last name') }}</label>
              </div>
              <div class="field">
                <input id="rApMat" name="ap_materno" type="text" placeholder=" "
                       value="{{ old('ap_materno') }}"
                       autocomplete="additional-name"
                       required>
                <label for="rApMat"><i class="fa-regular fa-user" aria-hidden="true"></i> {{ __('Mother\'s last name') }}</label>
              </div>
              <div class="field">
                <input id="rBirth" name="fecha_nacimiento" type="date" placeholder=" "
                       value="{{ old('fecha_nacimiento') }}"
                       max="{{ now()->subYears(18)->toDateString() }}"
                       min="1920-01-01"
                       autocomplete="bday"
                       required>
                <label for="rBirth"><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ __('Date of birth') }}</label>
              </div>
            </div>

            <div class="grid2">
              <div class="field">
                <input id="rEmail" name="correo" type="email" placeholder=" "
                       value="{{ old('correo') }}"
                       autocomplete="email"
                       required>
                <label for="rEmail"><i class="fa-regular fa-envelope" aria-hidden="true"></i> {{ __('Email') }}</label>
              </div>
              <div class="field">
                <input id="rEmail2" name="correo_confirmacion" type="email" placeholder=" "
                       value="{{ old('correo_confirmacion') }}"
                       autocomplete="email"
                       required>
                <label for="rEmail2"><i class="fa-regular fa-circle-check" aria-hidden="true"></i> {{ __('Confirm email') }}</label>
              </div>
            </div>

            <div class="grid2">
              <div class="field">
                <input id="rPass" name="password" type="password" placeholder=" "
                       autocomplete="new-password"
                       required>
                <label for="rPass"><i class="fa-solid fa-key" aria-hidden="true"></i> {{ __('Password') }}</label>
                <button type="button" class="eye" data-target="#rPass" aria-label="{{ __('Show / hide password') }}">
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
                <div class="strength" id="passStrength">
                  <span data-lvl="1"></span><span data-lvl="2"></span><span data-lvl="3"></span><span data-lvl="4"></span>
                  <small id="strengthLabel">{{ __('Strength:') }} —</small>
                </div>
              </div>
              <div class="field">
                <input id="rPass2" name="password_confirmacion" type="password" placeholder=" "
                       autocomplete="new-password"
                       required>
                <label for="rPass2"><i class="fa-regular fa-circle-check" aria-hidden="true"></i> {{ __('Confirm password') }}</label>
                <button type="button" class="eye" data-target="#rPass2" aria-label="{{ __('Show / hide password') }}">
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <label class="check mt8">
              <input type="checkbox" id="rTos" name="acepta_terminos" {{ old('acepta_terminos') ? 'checked' : '' }} required>
              <span>{{ __('I accept the Privacy Policy and Terms & Conditions') }}</span>
            </label>

            <button class="btn-primary w100" type="submit" id="btnCrearCuenta">
              <i class="fa-solid fa-user-check" aria-hidden="true"></i> {{ __('Create account') }}
            </button>
          </form>
        </div>
      </div>

      {{-- Columna imagen / branding --}}
      <aside class="auth-col brand-side">
        <div class="brand-pane">
          <img src="{{ asset('img/login2.webp') }}"
               alt=""
               width="800" height="600"
               loading="lazy"
               decoding="async">
          <div class="pane-gradient"></div>
          <div class="pane-info">
            <h3>{{ __('Rent today,') }}<br>{{ __('explore tomorrow') }}</h3>
            <ul>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('Fast & secure reservations') }}</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('Exclusive offers & upgrades') }}</li>
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('Manage your bookings') }}</li>
            </ul>
          </div>
        </div>
      </aside>

    </div>
  </section>
</main>

{{-- MODAL VERIFICACIÓN --}}
<div class="modal" id="verifyModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="vTitle">
    <button type="button" class="modal-close" id="vClose" aria-label="{{ __('Close') }}">
      <i class="fa-regular fa-circle-xmark" aria-hidden="true"></i>
    </button>
    <h3 id="vTitle"><i class="fa-regular fa-envelope" aria-hidden="true"></i> {{ __('Verify your email') }}</h3>
    <p>{{ __('We sent a code to') }} <strong id="verifyEmail">{{ session('correo_modal') ?? '' }}</strong>. {{ __('Enter the 6-digit code.') }}</p>

    <form id="formVerify" method="POST" action="{{ route('auth.verify') }}">
      @csrf
      <input type="hidden" name="correo" id="verifyEmailHidden" value="{{ session('correo_modal') ?? '' }}">

      <div class="code-input" id="codeInputs">
        <input name="c1" inputmode="numeric" maxlength="1" autocomplete="one-time-code" required>
        <input name="c2" inputmode="numeric" maxlength="1" required>
        <input name="c3" inputmode="numeric" maxlength="1" required>
        <input name="c4" inputmode="numeric" maxlength="1" required>
        <input name="c5" inputmode="numeric" maxlength="1" required>
        <input name="c6" inputmode="numeric" maxlength="1" required>
      </div>

      <div class="verify-actions">
        <button type="submit" class="btn-primary" id="btnVerify">
          <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('Verify') }}
        </button>
        <button type="submit" formaction="{{ route('auth.verify.resend') }}" class="btn-ghost" id="btnResend" disabled>
          {{ __('Resend code') }} <span id="resendTimer"></span>
        </button>
      </div>
    </form>
    <small class="hint">{{ __('Didn\'t receive it? Check "Promotions" or "Spam".') }}</small>
  </div>
</div>
@endsection

@section('js-vistaLogin')
  <script src="{{ asset('js/Login.js') }}" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js" defer></script>

  <script defer>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof alertify === 'undefined') return;

      // Configuración Alertify bilingüe
      const locale = document.documentElement.lang || 'es';
      alertify.defaults.glossary.title  = locale === 'en' ? 'Notification' : 'Notificación';
      alertify.defaults.glossary.ok     = locale === 'en' ? 'OK' : 'Aceptar';
      alertify.defaults.glossary.cancel = locale === 'en' ? 'Cancel' : 'Cancelar';
      alertify.defaults.glossary.close  = locale === 'en' ? 'Close' : 'Cerrar';
      alertify.defaults.notifier.position = 'top-center';

      // Mensajes del backend (escapados con  para evitar XSS)
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          alertify.error(@json($error));
        @endforeach
      @endif

      @if (session('error'))
        alertify.error(@json(session('error')));
      @endif

      @if (session('success'))
        alertify.success(@json(session('success')));
      @endif

      @if (session('session_expired'))
        alertify.warning(@json(session('session_expired')));
      @endif
    });
  </script>
@endsection
