@extends('layouts.Usuarios')

@section('Titulo', __('Sign in'))

@section('css-vistaLogin')
  <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
  {{-- Alertify CSS --}}
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

  {{-- 🔹 Metadatos usados por el JS para mostrar el modal automáticamente --}}
  @if (session('show_modal'))
    <meta name="show-modal" content="true">
    <meta name="correo-modal" content="{{ session('correo_modal') }}">
  @endif
@endsection

@section('contenidoLogin')
<main class="page">
  <!-- HERO -->
  <section class="hero hero-auth">
    <div class="hero-bg">
      <img src="{{ asset('img/login.png') }}" alt="hero">
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>{{ __('Welcome to') }} <span>{{ __('Viajero') }}</span></h1>
      <p>{{ __('Sign in or create an account to continue') }}</p>
    </div>
  </section>

  <!-- TARJETA LOGIN/REGISTER -->
  <section class="auth-container">
    <div class="auth-card-2col">

      <!-- Columna formulario -->
      <div class="auth-col form-side">
        <div class="segmented" id="tabs">
          <button class="seg-btn {{ old('form_type') === 'register' ? '' : 'active' }}" data-target="#panel-login">{{ __('Sign in') }}</button>
          <button class="seg-btn {{ old('form_type') === 'register' ? 'active' : '' }}" data-target="#panel-register">{{ __('Create account') }}</button>
          <span class="seg-slider"></span>
        </div>

        <!-- Panel LOGIN -->
        <div class="auth-panel {{ old('form_type') === 'register' ? '' : 'show' }}" id="panel-login">
          <form id="formLogin" method="POST" action="{{ route('auth.login') }}" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="login">

            <div class="field">
              <input id="loginUser" name="login" type="text" placeholder=" " value="{{ old('login') }}" required />
              <label for="loginUser"><i class="fa-regular fa-envelope"></i> {{ __('Email or Username') }}</label>
            </div>

            <div class="field">
              <input id="loginPass" name="password" type="password" placeholder=" " required />
              <label for="loginPass"><i class="fa-solid fa-lock"></i> {{ __('Password') }}</label>
              <button type="button" class="eye" data-target="#loginPass"><i class="fa-regular fa-eye"></i></button>
            </div>

            <div class="aux-row">
              <label class="check"><input type="checkbox" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }}><span>{{ __('Remember me') }}</span></label>
              <a class="link" id="forgotLink">{{ __('Forgot your password?') }}</a>
            </div>

            <button class="btn-primary w100" type="submit">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('Sign in') }}
            </button>
          </form>
        </div>

        <!-- Panel REGISTER -->
        <div class="auth-panel {{ old('form_type') === 'register' ? 'show' : '' }}" id="panel-register">
          <form id="formRegister" method="POST" action="{{ route('auth.register') }}" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="register">

            <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <input id="rName" name="nombres" type="text" placeholder=" " value="{{ old('nombres') }}" required />
                <label for="rName"><i class="fa-regular fa-user"></i> {{ __('First name') }}</label>
              </div>
              <div class="field">
                <input id="rApPat" name="ap_paterno" type="text" placeholder=" " value="{{ old('ap_paterno') }}" required />
                <label for="rApPat"><i class="fa-regular fa-user"></i> {{ __('Last name') }}</label>
              </div>
              <div class="field">
                <input id="rApMat" name="ap_materno" type="text" placeholder=" " value="{{ old('ap_materno') }}" required />
                <label for="rApMat"><i class="fa-regular fa-user"></i> {{ __('Mother\'s last name') }}</label>
              </div>
              <div class="field">
                <input id="rBirth" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}" placeholder=" " required />
                <label for="rBirth"><i class="fa-regular fa-calendar"></i> {{ __('Date of birth') }}</label>
              </div>
            </div>

            <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <input id="rEmail" name="correo" type="email" placeholder=" " value="{{ old('correo') }}" required />
                <label for="rEmail"><i class="fa-regular fa-envelope"></i> {{ __('Email') }}</label>
              </div>
              <div class="field">
                <input id="rEmail2" name="correo_confirmacion" type="email" placeholder=" " value="{{ old('correo_confirmacion') }}" required />
                <label for="rEmail2"><i class="fa-regular fa-circle-check"></i> {{ __('Confirm email') }}</label>
              </div>
            </div>

            <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <input id="rPass" name="password" type="password" placeholder=" " required />
                <label for="rPass"><i class="fa-solid fa-key"></i> {{ __('Password') }}</label>
                <button type="button" class="eye" data-target="#rPass"><i class="fa-regular fa-eye"></i></button>
                <div class="strength" id="passStrength">
                  <span data-lvl="1"></span><span data-lvl="2"></span><span data-lvl="3"></span><span data-lvl="4"></span>
                  <small id="strengthLabel">{{ __('Strength:') }} —</small>
                </div>
              </div>
              <div class="field">
                <input id="rPass2" name="password_confirmacion" type="password" placeholder=" " required />
                <label for="rPass2"><i class="fa-regular fa-circle-check"></i> {{ __('Confirm password') }}</label>
                <button type="button" class="eye" data-target="#rPass2"><i class="fa-regular fa-eye"></i></button>
              </div>
            </div>

            <label class="check mt8">
              <input type="checkbox" id="rTos" name="acepta_terminos" {{ old('acepta_terminos') ? 'checked' : '' }} required>
              <span>{{ __('I accept the Privacy Policy and Terms & Conditions') }}</span>
            </label>

            <button class="btn-primary w100" type="submit" id="btnCrearCuenta">
              <i class="fa-solid fa-user-check"></i> {{ __('Create account') }}
            </button>
          </form>
        </div>
      </div>

      <!-- Columna imagen / branding -->
      <aside class="auth-col brand-side">
        <div class="brand-pane">
          <img src="{{ asset('img/login2.png') }}" alt="auto">
          <div class="pane-gradient"></div>
          <div class="pane-info">
            <h3>{{ __('Rent today,') }}<br>{{ __('explore tomorrow') }}</h3>
            <ul>
              <li><i class="fa-solid fa-circle-check"></i> {{ __('Fast & secure reservations') }}</li>
              <li><i class="fa-solid fa-circle-check"></i> {{ __('Exclusive offers & upgrades') }}</li>
              <li><i class="fa-solid fa-circle-check"></i> {{ __('Manage your bookings') }}</li>
            </ul>
          </div>
        </div>
      </aside>

    </div>
  </section>
</main>

<!-- MODAL VERIFICACIÓN -->
<div class="modal" id="verifyModal" aria-hidden="true" style="display:none;">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="vTitle">
    <button class="modal-close" id="vClose"><i class="fa-regular fa-circle-xmark"></i></button>
    <h3 id="vTitle"><i class="fa-regular fa-envelope"></i> {{ __('Verify your email') }}</h3>
    <p>{{ __('We sent a code to') }} <strong id="verifyEmail"></strong>. {{ __('Enter the 6-digit code.') }}</p>

    <form id="formVerify" method="POST" action="{{ route('auth.verify') }}">
      @csrf
      <input type="hidden" name="correo" id="verifyEmailHidden">
      <div class="code-input" id="codeInputs" style="display:flex;gap:8px;justify-content:center;">
        <input name="c1" inputmode="numeric" maxlength="1" required>
        <input name="c2" inputmode="numeric" maxlength="1" required>
        <input name="c3" inputmode="numeric" maxlength="1" required>
        <input name="c4" inputmode="numeric" maxlength="1" required>
        <input name="c5" inputmode="numeric" maxlength="1" required>
        <input name="c6" inputmode="numeric" maxlength="1" required>
      </div>
      <div class="verify-actions" style="margin-top:15px;display:flex;gap:10px;justify-content:center;">
        <button type="submit" class="btn-primary" id="btnVerify">
          <i class="fa-solid fa-circle-check"></i> {{ __('Verify') }}
        </button>
        <button type="submit" formaction="{{ route('auth.verify.resend') }}" class="btn-ghost" id="btnResend" disabled>
          {{ __('Resend code') }} <span id="resendTimer">(30s)</span>
        </button>
      </div>
    </form>
    <small class="hint">{{ __('Didn\'t receive it? Check "Promotions" or "Spam".') }}</small>
  </div>
</div>
@endsection

@section('js-vistaLogin')
  <script src="{{ asset('js/Login.js') }}" defer></script>
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // === Traducción y configuración Alertify ===
      const locale = document.documentElement.lang || 'es';
      alertify.defaults.glossary.title = locale === 'en' ? 'Notification' : 'Notificación';
      alertify.defaults.glossary.ok = locale === 'en' ? 'OK' : 'Aceptar';
      alertify.defaults.glossary.cancel = locale === 'en' ? 'Cancel' : 'Cancelar';
      alertify.defaults.glossary.close = locale === 'en' ? 'Close' : 'Cerrar';
      alertify.defaults.notifier.position = 'top-center';

      // === Mostrar mensajes del backend ===
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          alertify.error("{{ $error }}");
        @endforeach
      @endif

      @if (session('error'))
        alertify.error("{{ session('error') }}");
      @endif

      @if (session('success'))
        alertify.success("{{ session('success') }}");
      @endif

      // 🔔 Mensaje cuando la sesión expiró (middleware SesionActiva)
      @if (session('session_expired'))
        alertify.warning("{{ session('session_expired') }}");
      @endif
    });
  </script>
@endsection
