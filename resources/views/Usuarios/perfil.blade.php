@extends('layouts.Usuarios')

@section('Titulo', 'Mi Perfil')

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
      <h1>Mi <span>perfil</span></h1>
      <p>Administra tu información, reservaciones y métodos de pago</p>
    </div>
  </section>

  <section class="dash">
    <aside class="card pad side">
      <h3><i class="fa-regular fa-circle-user" style="color:var(--brand)"></i> Cuenta</h3>
      <div class="s-menu" id="sMenu">
        <button class="s-btn active" data-target="#pPerfil"><i class="fa-solid fa-id-card"></i> Mi perfil</button>
        <button class="s-btn" data-target="#pReservas"><i class="fa-solid fa-calendar-check"></i> Mis reservaciones</button>
        <button class="s-btn" data-target="#pTarjetas"><i class="fa-solid fa-credit-card"></i> Mis tarjetas</button>
      </div>
      <button class="logout" id="btnLogout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
    </aside>

    <div class="stack">
      {{-- Panel: Mi perfil --}}
      <section class="card pad panel show" id="pPerfil">
        <h3>Mi perfil</h3>
        <p class="subtitle">Mantén tus datos al día para agilizar tus reservas</p>

        <form id="formPerfil">
          <div class="grid2">
            <div class="field">
              <label>Nombre completo</label>
              <input id="pfName" placeholder="Tu nombre y apellidos" disabled>
            </div>
            <div class="field">
              <label>Correo</label>
              <input id="pfEmail" type="email" placeholder="tucorreo@dominio.com" disabled>
            </div>
            <div class="field">
              <label>Teléfono</label>
              <input id="pfPhone" type="tel" placeholder="55 1234 5678" disabled>
            </div>

            <div class="field">
              <label>Fecha de nacimiento</label>
              <div class="nice-date disabled" id="birthPicker">
                <i class="fa-regular fa-calendar"></i>
                <input id="pfBirthDisplay" type="text" placeholder="dd/mm/aaaa" readonly>
                <div class="cal-pop" aria-hidden="true"></div>
              </div>
              <input id="pfBirth" type="hidden">
            </div>

            <div class="field">
              <label>Edad</label>
              <input id="pfAge" placeholder="—" disabled>
            </div>
            <div class="field">
              <label>Ubicación</label>
              <input id="pfLoc" placeholder="Ciudad, Estado" disabled>
            </div>
            <div class="field">
              <label>No. de membresía</label>
              <input id="pfMember" placeholder="VJ-XXXX-XXXX" disabled>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-ghost" id="btnEdit">
              <i class="fa-regular fa-pen-to-square"></i> Editar
            </button>
            <button type="submit" class="btn btn-primary" id="btnSave" disabled>
              <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
          </div>
        </form>
      </section>

      {{-- Panel: Reservas --}}
      <section class="card pad panel" id="pReservas">
        <h3>Mis reservaciones</h3>
        <p class="subtitle">Consulta el historial y el estado de tus reservas</p>

        <div class="filters" id="resFilters">
          <button class="pill active" data-status="all">Todas</button>
          <button class="pill" data-status="pendiente">Pendientes</button>
          <button class="pill" data-status="activa">Activas</button>
          <button class="pill" data-status="finalizada">Finalizadas</button>
        </div>

        <div class="table-wrap">
          <table class="table" id="resTable" aria-label="Reservaciones">
            <thead>
              <tr>
                <th>Contrato</th><th>Auto</th><th>Fechas</th><th>Estatus</th>
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
            <h3>Mis tarjetas</h3>
            <p class="subtitle">Administra tus métodos de pago guardados</p>
          </div>
          <button class="btn btn-primary" id="btnAddCard">
            <i class="fa-solid fa-plus"></i> Agregar tarjeta
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
    <button class="modal-close" id="cardClose" aria-label="Cerrar"><i class="fa-regular fa-circle-xmark"></i></button>
    <h3 class="modal-title" id="cardModalTitle"><i class="fa-solid fa-credit-card"></i> Método de pago</h3>
    <form id="cardForm">
      <div class="field">
        <label>Nombre en la tarjeta</label>
        <input id="ccName" required placeholder="Como aparece en la tarjeta">
      </div>
      <div class="grid2">
        <div class="field">
          <label>Número</label>
          <input id="ccNumber" inputmode="numeric" maxlength="19" required placeholder="#### #### #### ####">
        </div>
        <div class="field">
          <label>Marca</label>
          <select id="ccBrand" required>
            <option value="visa">Visa</option>
            <option value="mastercard">Mastercard</option>
            <option value="amex">Amex</option>
          </select>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label>Expiración (MM/AA)</label>
          <input id="ccExp" placeholder="MM/AA" maxlength="5" required>
        </div>
        <div class="field">
          <label>Alias (opcional)</label>
          <input id="ccAlias" placeholder="p.ej. Personal, Empresa">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" id="cardCancel">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- 🔹 Modal: Cerrar Sesión --}}
<div class="modal" id="logoutModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="loTitle" aria-describedby="loDesc">
    <button class="modal-close" id="loClose" aria-label="Cerrar"><i class="fa-regular fa-circle-xmark"></i></button>
    <div class="logout-card-head">
      <div class="lo-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
      <div>
        <h3 class="lo-title" id="loTitle">¿Cerrar sesión?</h3>
        <p class="lo-sub" id="loDesc">Tu sesión se cerrará en este dispositivo.</p>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-light" id="logoutCancel"><i class="fa-regular fa-circle-xmark"></i> Cancelar</button>
      <button class="btn btn-danger" id="logoutConfirm"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
    </div>
  </div>
</div>

{{-- 🔹 Toast de confirmación --}}
<div class="toast" id="toast"><i class="fa-solid fa-check"></i> Cambios guardados</div>
@endsection

{{-- 🧠 Scripts específicos --}}
@section('js-vistaPerfil')
<script src="{{ asset('js/Perfil.js') }}"></script>
@endsection
