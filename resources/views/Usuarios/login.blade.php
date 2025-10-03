@extends('layouts.Usuarios')
@section('Titulo', 'Iniciar sesión')
    @section('css-vistaLogin')
        <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
    @endsection
    @section('contenidoLogin')
         <main class="page">

    <section class="hero hero-auth">
      <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1518555539400-0b67c3e6f3e3?q=80&w=1600&auto=format&fit=crop" alt="hero">
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Bienvenido a <span>Viajero</span></h1>
        <p>Inicia sesión o crea tu cuenta para continuar</p>
      </div>
    </section>

    <section class="auth-container">
      <div class="auth-card-2col">

        <div class="auth-col form-side">
          <div class="segmented" id="tabs">
            <button class="seg-btn active" data-target="#panel-login">Iniciar sesión</button>
            <button class="seg-btn" data-target="#panel-register">Crear cuenta</button>
            <span class="seg-slider"></span>
          </div>

          <div class="auth-panel show" id="panel-login">
            <form id="formLogin" novalidate>
              <div class="field">
                <input id="loginUser" type="text" placeholder=" " required />
                <label for="loginUser"><i class="fa-regular fa-envelope"></i> Correo o Usuario</label>
                <small class="msg"></small>
              </div>

              <div class="field">
                <input id="loginPass" type="password" placeholder=" " required />
                <label for="loginPass"><i class="fa-solid fa-lock"></i> Contraseña</label>
                <button type="button" class="eye" data-target="#loginPass"><i class="fa-regular fa-eye"></i></button>
                <small class="msg"></small>
              </div>

              <div class="aux-row">
                <label class="check"><input type="checkbox" id="rememberMe"><span>Recordarme</span></label>
                <a class="link" id="forgotLink">¿Olvidaste tu contraseña?</a>
              </div>

              <button class="btn-primary w100" type="submit">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
              </button>
            </form>
          </div>

          <div class="auth-panel" id="panel-register">
            <form id="formRegister" novalidate>
              <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field">
                  <input id="rName" type="text" placeholder=" " required />
                  <label for="rName"><i class="fa-regular fa-user"></i> Nombre</label>
                  <small class="msg"></small>
                </div>
                <div class="field">
                  <input id="rApPat" type="text" placeholder=" " required />
                  <label for="rApPat"><i class="fa-regular fa-user"></i> Apellido paterno</label>
                  <small class="msg"></small>
                </div>
                <div class="field">
                  <input id="rApMat" type="text" placeholder=" " required />
                  <label for="rApMat"><i class="fa-regular fa-user"></i> Apellido materno</label>
                  <small class="msg"></small>
                </div>
                <div class="field">
                  <input id="rBirth" type="date" placeholder=" " required />
                  <label for="rBirth"><i class="fa-regular fa-calendar"></i> Fecha de nacimiento</label>
                  <small class="msg"></small>
                </div>
              </div>

              <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field">
                  <input id="rEmail" type="email" placeholder=" " required />
                  <label for="rEmail"><i class="fa-regular fa-envelope"></i> Correo</label>
                  <small class="msg"></small>
                </div>
                <div class="field">
                  <input id="rEmail2" type="email" placeholder=" " required />
                  <label for="rEmail2"><i class="fa-regular fa-circle-check"></i> Confirmación de correo</label>
                  <small class="msg"></small>
                </div>
              </div>

              <div class="grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field">
                  <input id="rPass" type="password" placeholder=" " required />
                  <label for="rPass"><i class="fa-solid fa-key"></i> Contraseña</label>
                  <button type="button" class="eye" data-target="#rPass"><i class="fa-regular fa-eye"></i></button>
                  <div class="strength" id="passStrength">
                    <span data-lvl="1"></span><span data-lvl="2"></span><span data-lvl="3"></span><span data-lvl="4"></span>
                    <small id="strengthLabel">Fortaleza: —</small>
                  </div>
                  <small class="msg"></small>
                </div>
                <div class="field">
                  <input id="rPass2" type="password" placeholder=" " required />
                  <label for="rPass2"><i class="fa-regular fa-circle-check"></i> Confirmación de contraseña</label>
                  <button type="button" class="eye" data-target="#rPass2"><i class="fa-regular fa-eye"></i></button>
                  <small class="msg"></small>
                </div>
              </div>

              <label class="check mt8">
                <input type="checkbox" id="rTos" required>
                <span>Acepto el Aviso de Privacidad y los Términos y Condiciones</span>
              </label>

              <button class="btn-primary w100" type="submit">
                <i class="fa-solid fa-user-check"></i> Crear cuenta
              </button>
            </form>
          </div>
        </div>

        <aside class="auth-col brand-side">
          <div class="brand-pane">
            <img src="https://images.unsplash.com/photo-1549921296-3c380e9a5b09?q=80&w=1200&auto=format&fit=crop" alt="auto" />
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

  <div class="modal" id="verifyModal" aria-hidden="true">
    <div class="modal-backdrop"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="vTitle">
      <button class="modal-close" id="vClose"><i class="fa-regular fa-circle-xmark"></i></button>
      <h3 id="vTitle"><i class="fa-regular fa-envelope"></i> Verifica tu correo</h3>
      <p>Enviamos un código a <strong id="verifyEmail"></strong>. Ingresa el código de 6 dígitos.</p>
      <div class="code-input" id="codeInputs">
        <input inputmode="numeric" maxlength="1">
        <input inputmode="numeric" maxlength="1">
        <input inputmode="numeric" maxlength="1">
        <input inputmode="numeric" maxlength="1">
        <input inputmode="numeric" maxlength="1">
        <input inputmode="numeric" maxlength="1">
      </div>
      <div class="verify-actions">
        <button class="btn-primary" id="btnVerify"><i class="fa-solid fa-circle-check"></i> Verificar</button>
        <button class="btn-ghost" id="btnResend" disabled>Reenviar código <span id="resendTimer">(30s)</span></button>
      </div>
      <small class="hint">¿No llegó? Revisa “Promociones” o “Spam”.</small>
      <div class="mock-mail" id="mockMail" aria-hidden="true"></div>
    </div>
  </div>

  <div class="welcome-modal" id="welcomeModal" aria-hidden="true">
    <div class="wm-backdrop"></div>
    <div class="wm-card" role="dialog" aria-modal="true" aria-labelledby="wmTitle">
      <div class="wm-head">
        <div class="wm-icon"><i class="fa-solid fa-check"></i></div>
        <div>
          <h3 class="wm-title" id="wmTitle">¡Bienvenido!</h3>
          <p class="wm-sub" id="wmSub">Vamos a tu reserva</p>
        </div>
      </div>
      <div class="wm-progress" aria-hidden="true"><span id="wmBar"></span></div>
      <div class="wm-actions">
        <span class="wm-count" id="wmCount">Redirigiendo en 2 s…</span>
        <button class="btn-now" id="wmNow"><i class="fa-solid fa-arrow-right"></i> Ir ahora</button>
      </div>
    </div>
    @section('js-vistaLogin')
  <script src="{{ asset('js/Login.js') }}" defer></script>
@endsection

@endsection
