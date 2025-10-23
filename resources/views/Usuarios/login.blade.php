@extends('layouts.Usuarios')

@section('Titulo', 'Iniciar sesión')

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
      <h1>Bienvenido a <span>Viajero</span></h1>
      <p>Inicia sesión o crea tu cuenta para continuar</p>
    </div>
  </section>

  <!-- TARJETA LOGIN/REGISTER -->
  <section class="auth-container">
    <div class="auth-card-2col">

      <!-- Columna formulario -->
      <div class="auth-col form-side">
        <div class="segmented" id="tabs">
          <button class="seg-btn {{ old('form_type') === 'register' ? '' : 'active' }}" data-target="#panel-login">Iniciar sesión</button>
          <button class="seg-btn {{ old('form_type') === 'register' ? 'active' : '' }}" data-target="#panel-register">Crear cuenta</button>
          <span class="seg-slider"></span>
        </div>

        <!-- Panel LOGIN -->
        <div class="auth-panel {{ old('form_type') === 'register' ? '' : 'show' }}" id="panel-login">
          <form id="formLogin" method="POST" action="{{ route('auth.login') }}" novalidate>
            @csrf
            <input type="hidden" name="form_type" value="login">

            <div class="field">
              <input id="loginUser" name="login" type="text" placeholder=" " value="{{ old('login') }}" required />
              <label for="loginUser"><i class="fa-regular fa-envelope"></i> Correo o Usuario</label>
            </div>

            <div class="field">
              <input id="loginPass" name="password" type="password" placeholder=" " required />
              <label for="loginPass"><i class="fa-solid fa-lock"></i> Contraseña</label>
              <button type="button" class="eye" data-target="#loginPass"><i class="fa-regular fa-eye"></i></button>
            </div>

            <div class="aux-row">
              <label class="check"><input type="checkbox" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }}><span>Recordarme</span></label>
              <a class="link" id="forgotLink">¿Olvidaste tu contraseña?</a>
            </div>

            <button class="btn-primary w100" type="submit">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
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
                <label for="rName"><i class="fa-regular fa-user"></i> Nombre</label>
              </div>
              <div class="field">
                <input id="rApPat" name="ap_paterno" type="text" placeholder=" " value="{{ old('ap_paterno') }}" required />
                <label for="rApPat"><i class="fa-regular fa-user"></i> Apellido paterno</label>
              </div>
              <div class="field">
                <input id="rApMat" name="ap_materno" type="text" placeholder=" " value="{{ old('ap_materno') }}" required />
                <label for="rApMat"><i class="fa-regular fa-user"></i> Apellido materno</label>
              </div>
              <div class="field">
                <input id="rBirth" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}" placeholder=" " required />
                <label for="rBirth"><i class="fa-regular fa-calendar"></i> Fecha de nacimiento</label>
              </div>
            </div>

            <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <input id="rEmail" name="correo" type="email" placeholder=" " value="{{ old('correo') }}" required />
                <label for="rEmail"><i class="fa-regular fa-envelope"></i> Correo</label>
              </div>
              <div class="field">
                <input id="rEmail2" name="correo_confirmacion" type="email" placeholder=" " value="{{ old('correo_confirmacion') }}" required />
                <label for="rEmail2"><i class="fa-regular fa-circle-check"></i> Confirmación de correo</label>
              </div>
            </div>

            <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <input id="rPass" name="password" type="password" placeholder=" " required />
                <label for="rPass"><i class="fa-solid fa-key"></i> Contraseña</label>
                <button type="button" class="eye" data-target="#rPass"><i class="fa-regular fa-eye"></i></button>
                <div class="strength" id="passStrength">
                  <span data-lvl="1"></span><span data-lvl="2"></span><span data-lvl="3"></span><span data-lvl="4"></span>
                  <small id="strengthLabel">Fortaleza: —</small>
                </div>
              </div>
              <div class="field">
                <input id="rPass2" name="password_confirmacion" type="password" placeholder=" " required />
                <label for="rPass2"><i class="fa-regular fa-circle-check"></i> Confirmación de contraseña</label>
                <button type="button" class="eye" data-target="#rPass2"><i class="fa-regular fa-eye"></i></button>
              </div>
            </div>

            <label class="check mt8">
              <input type="checkbox" id="rTos" name="acepta_terminos" {{ old('acepta_terminos') ? 'checked' : '' }} required>
              <span>Acepto el Aviso de Privacidad y los Términos y Condiciones</span>
            </label>

            <button class="btn-primary w100" type="submit" id="btnCrearCuenta">
              <i class="fa-solid fa-user-check"></i> Crear cuenta
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
            <h3>Renta hoy,<br>explora mañana</h3>
            <ul>
              <li><i class="fa-solid fa-circle-check"></i> Reservas rápidas y seguras</li>
              <li><i class="fa-solid fa-circle-check"></i> Ofertas y upgrades exclusivos</li>
              <li><i class="fa-solid fa-circle-check"></i> Administra tus reservas</li>
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
    <h3 id="vTitle"><i class="fa-regular fa-envelope"></i> Verifica tu correo</h3>
    <p>Enviamos un código a <strong id="verifyEmail"></strong>. Ingresa el código de 6 dígitos.</p>

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
          <i class="fa-solid fa-circle-check"></i> Verificar
        </button>
        <button type="submit" formaction="{{ route('auth.verify.resend') }}" class="btn-ghost" id="btnResend" disabled>
          Reenviar código <span id="resendTimer">(30s)</span>
        </button>
      </div>
    </form>
    <small class="hint">¿No llegó? Revisa “Promociones” o “Spam”.</small>
  </div>
</div>
@endsection

@section('js-vistaLogin')
  <script src="{{ asset('js/Login.js') }}" defer></script>
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // === Traducción y configuración Alertify ===
      alertify.defaults.glossary.title = 'Notificación';
      alertify.defaults.glossary.ok = 'Aceptar';
      alertify.defaults.glossary.cancel = 'Cancelar';
      alertify.defaults.glossary.close = 'Cerrar';
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
    });
  </script>
@endsection
