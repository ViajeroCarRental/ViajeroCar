@extends('layouts.Usuarios')
@section('Titulo', 'Perfil de usuario')
    @section('css-vistaPerfil')
        <link rel="stylesheet" href="{{ asset('css/Perfil.css') }}">
    @endsection
    @section('contenidoPerfil')
    <main class="page">
    <section class="hero">
      <div class="hero-bg">
        <img src="{{ asset('img/perfil.png') }}" alt="hero">
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
              <button type="button" class="btn btn-ghost" id="btnEdit"><i class="fa-regular fa-pen-to-square"></i> Editar</button>
              <button type="submit" class="btn btn-primary" id="btnSave" disabled><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
          </form>
        </section>

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

        <section class="card pad panel" id="pTarjetas">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <h3>Mis tarjetas</h3>
              <p class="subtitle">Administra tus métodos de pago guardados</p>
            </div>
            <button class="btn btn-primary" id="btnAddCard"><i class="fa-solid fa-plus"></i> Agregar tarjeta</button>
          </div>

          <div class="cards" id="cardsWrap"></div>
        </section>
      </div>
    </section>
  </main> 
    @section('js-vistaPerfil')
        <script src="{{ asset('js/Perfil.js') }}"></script>
    @endsection
@endsection
