@extends('layouts.Usuarios')

@section('Titulo','Reservaciones')

@section('css-vistaReservaciones')
    <link rel="stylesheet" href="{{ asset('css/reservaciones.css') }}">
@endsection

 @section('contenidoReservaciones')
    <main class="page">
    <section class="steps-wrap">
      <div class="steps-card">
        <div class="steps-header">
          <div class="step-item done" data-step="1">
            <span class="bubble">1</span>
            <span class="label">Elige lugar y fecha</span>
          </div>
          <div class="step-item active" data-step="2">
            <span class="bubble">2</span>
            <span class="label">Selecciona tu auto</span>
          </div>
          <div class="step-item" data-step="3">
            <span class="bubble">3</span>
            <span class="label">Complementos</span>
          </div>
          <div class="step-item" data-step="4">
            <span class="bubble">4</span>
            <span class="label">Resumen de tu reserva</span>
          </div>
          <div class="progress-line"><span class="progress-fill" id="progressFill"></span></div>
        </div>

        <div class="booking-brief">
          <div class="brief-left">
            <div class="brief-title"><i class="fa-solid fa-location-dot"></i> <span id="briefLoc">Querétaro Aeropuerto</span></div>
            <div class="brief-dates">
              <div><strong>Entrega:</strong> <span id="briefStart">—</span></div>
              <div><strong>Devolución:</strong> <span id="briefEnd">—</span></div>
            </div>
          </div>
          <div class="brief-right">
            <button class="link" id="toggleEdit"><i class="fa-solid fa-pen-to-square"></i> Modificar</button>
          </div>
        </div>

        <form class="edit-panel" id="editPanel">
          <div class="grid">
            <div class="field">
              <label>Ubicación</label>
              <select id="loc">
                <option>Querétaro Aeropuerto</option>
                <option>Central Park Querétaro</option>
                <option>Aeropuerto de León</option>
              </select>
            </div>
            <div class="field">
              <label>Entrega</label>
              <input id="start" type="text" placeholder="Selecciona fecha y hora">
            </div>
            <div class="field">
              <label>Devolución</label>
              <input id="end" type="text" placeholder="Selecciona fecha y hora">
            </div>
            <div class="field actions">
              <button class="btn btn-secondary" type="button" id="cancelEdit">Cancelar</button>
              <button class="btn btn-primary" type="submit">Aplicar cambios</button>
            </div>
          </div>
        </form>
      </div>

      <div class="section-divider"><span class="tag">Autos disponibles</span></div>
    </section>

    <section class="results" id="step2">
      <div class="step-back">
        <button class="btn btn-ghost" id="backTo1">← Regresar</button>
      </div>

      <article class="r-card"
        data-name="Chevrolet Aveo o similar" data-cat="C" data-type="compacto"
        data-pay-counter="11165" data-pay-pre="6380">
        <div class="stock-pill">¡Quedan <span>3</span> autos!</div>
        <div class="r-media">
          <img src="https://images.unsplash.com/photo-1619767886558-efdc259cde1a?q=80&w=1200&auto=format&fit=crop" alt="Aveo">
        </div>
        <div class="r-body">
          <h3>Chevrolet <strong>Aveo</strong> o similar</h3>
          <div class="subtitle">COMPACTO | CATEGORÍA C</div>
          <ul class="icons">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 2</li>
            <li><i class="fa-solid fa-door-open"></i> 4</li>
            <li><i class="fa-solid fa-gear"></i> M</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incl">KM ilimitados | Relevo de Responsabilidad (LI)</p>
        </div>
        <div class="r-price">
          <div class="p-col">
            <div class="p-old">$15,950 <small>MXN</small></div>
            <div class="p-new" data-plan-label="En mostrador">$11,165 <small>MXN</small></div>
            <div class="p-save">Ahorra $4,785 MXN</div>
            <button class="btn btn-gray pick" data-plan="mostrador">En mostrador</button>
          </div>
          <div class="p-col">
            <div class="p-old">$15,950 <small>MXN</small></div>
            <div class="p-new p-accent" data-plan-label="Prepago">$6,380 <small>MXN</small></div>
            <div class="p-save">Ahorra $9,570 MXN</div>
            <button class="btn btn-primary pick" data-plan="prepago">Prepago</button>
          </div>

          <div class="member-offer">
            <div class="mo-head"><i class="fa-solid fa-id-card"></i> Miembro preferente</div>
            <div class="mo-grid">
              <div class="mcol">
                <div class="m-label">Mostrador (socio)</div>
                <div class="m-price" data-member="mostrador">—</div>
                <div class="m-save" data-save="mostrador">—</div>
                <button class="btn btn-light m-btn pick-member" data-plan="mostrador">En mostrador</button>
              </div>
              <div class="mcol">
                <div class="m-label">Prepago (socio)</div>
                <div class="m-price" data-member="prepago">—</div>
                <div class="m-save" data-save="prepago">—</div>
                <button class="btn btn-primary m-btn pick-member" data-plan="prepago">Pago Prepago</button>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="r-card"
        data-name="Volkswagen Virtus o similar" data-cat="D" data-type="intermedio"
        data-pay-counter="14165" data-pay-pre="8380">
        <div class="stock-pill">¡Quedan <span>3</span> autos!</div>
        <div class="r-media">
          <img src="https://images.unsplash.com/photo-1606661421950-0f23d4f9f4ce?q=80&w=1200&auto=format&fit=crop" alt="Virtus">
        </div>
        <div class="r-body">
          <h3>Volkswagen <strong>Virtus</strong> o similar</h3>
          <div class="subtitle">INTERMEDIO | CATEGORÍA D</div>
          <ul class="icons">
            <li><i class="fa-solid fa-user-group"></i> 5</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> 3</li>
            <li><i class="fa-solid fa-door-open"></i> 4</li>
            <li><i class="fa-solid fa-gear"></i> A</li>
            <li><i class="fa-regular fa-snowflake"></i> A/C</li>
          </ul>
          <p class="incl">KM ilimitados | Relevo de Responsabilidad (LI)</p>
        </div>
        <div class="r-price">
          <div class="p-col">
            <div class="p-old">$18,950 <small>MXN</small></div>
            <div class="p-new" data-plan-label="En mostrador">$14,165 <small>MXN</small></div>
            <div class="p-save">Ahorra $4,785 MXN</div>
            <button class="btn btn-gray pick" data-plan="mostrador">En mostrador</button>
          </div>
          <div class="p-col">
            <div class="p-old">$18,950 <small>MXN</small></div>
            <div class="p-new p-accent" data-plan-label="Prepago">$8,380 <small>MXN</small></div>
            <div class="p-save">Ahorra $9,570 MXN</div>
            <button class="btn btn-primary pick" data-plan="prepago">Prepago</button>
          </div>

          <div class="member-offer">
            <div class="mo-head"><i class="fa-solid fa-id-card"></i> Miembro preferente</div>
            <div class="mo-grid">
              <div class="mcol">
                <div class="m-label">Mostrador (socio)</div>
                <div class="m-price" data-member="mostrador">—</div>
                <div class="m-save" data-save="mostrador">—</div>
                <button class="btn btn-light m-btn pick-member" data-plan="mostrador">Reservar socio</button>
              </div>
              <div class="mcol">
                <div class="m-label">Prepago (socio)</div>
                <div class="m-price" data-member="prepago">—</div>
                <div class="m-save" data-save="prepago">—</div>
                <button class="btn btn-primary m-btn pick-member" data-plan="prepago">Reservar socio</button>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="r-card"
        data-name="Kia Sportage o similar" data-cat="F" data-type="suv"
        data-pay-counter="19990" data-pay-pre="12990">
        <div class="stock-pill">¡Quedan <span>2</span> autos!</div>
        <div class="r-media">
          <img src="https://images.unsplash.com/photo-1603380355075-45a9cd9bba56?q=80&w=1200&auto=format&fit=crop" alt="Sportage">
        </div>
        <div class="r-body">
          <h3>Kia <strong>Sportage</strong> o similar</h3>
          <div class="subtitle">SUV | CATEGORÍA F</div>
        </div>
        <div class="r-price">
          <div class="p-col">
            <div class="p-old">$23,990 <small>MXN</small></div>
            <div class="p-new" data-plan-label="En mostrador">$19,990 <small>MXN</small></div>
            <div class="p-save">Ahorra $4,000 MXN</div>
            <button class="btn btn-gray pick" data-plan="mostrador">En mostrador</button>
          </div>

          <div class="member-offer">
            <div class="mo-head"><i class="fa-solid fa-id-card"></i> Miembro preferente</div>
            <div class="mo-grid">
              <div class="mcol">
                <div class="m-label">Mostrador (socio)</div>
                <div class="m-price" data-member="mostrador">—</div>
                <div class="m-save" data-save="mostrador">—</div>
                <button class="btn btn-light m-btn pick-member" data-plan="mostrador">Reservar socio</button>
              </div>
              <div class="mcol">
                <div class="m-label">Prepago (socio)</div>
                <div class="m-price" data-member="prepago">—</div>
                <div class="m-save" data-save="prepago">—</div>
                <button class="btn btn-primary m-btn pick-member" data-plan="prepago">Reservar socio</button>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="r-card"
        data-name="BMW Serie 3 o similar" data-cat="L" data-type="lujo"
        data-pay-counter="18990" data-pay-pre="14990">
        <div class="stock-pill">¡Queda <span>1</span> auto!</div>
        <div class="r-media">
          <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?q=80&w=1200&auto=format&fit=crop" alt="BMW 3">
        </div>
        <div class="r-body">
          <h3>BMW <strong>Serie 3</strong> o similar</h3>
          <div class="subtitle">LUJO | CATEGORÍA L</div>
        </div>
        <div class="r-price">
          <div class="p-col">
            <div class="p-old">$22,990 <small>MXN</small></div>
            <div class="p-new" data-plan-label="En mostrador">$18,990 <small>MXN</small></div>
            <div class="p-save">Ahorra $4,000 MXN</div>
            <button class="btn btn-gray pick" data-plan="mostrador">En mostrador</button>
          </div>

          <div class="member-offer">
            <div class="mo-head"><i class="fa-solid fa-id-card"></i> Miembro preferente</div>
            <div class="mo-grid">
              <div class="mcol">
                <div class="m-label">Mostrador (socio)</div>
                <div class="m-price" data-member="mostrador">—</div>
                <div class="m-save" data-save="mostrador">—</div>
                <button class="btn btn-light m-btn pick-member" data-plan="mostrador">Reservar socio</button>
              </div>
              <div class="mcol">
                <div class="m-label">Prepago (socio)</div>
                <div class="m-price" data-member="prepago">—</div>
                <div class="m-save" data-save="prepago">—</div>
                <button class="btn btn-primary m-btn pick-member" data-plan="prepago">Reservar socio</button>
              </div>
            </div>
          </div>
        </div>
      </article>
    </section>

    <section id="step3" class="addons hidden">
      <div class="step-back"><button class="btn btn-ghost" id="backTo2">← Regresar</button></div>

      <h3 class="addons-title">Elige tus complementos</h3>

      <div class="addons-grid">
        <div class="addon-card" data-id="silla" data-name="Silla de seguridad para niños" data-price="150">
          <div class="addon-icon"><i class="fa-solid fa-child"></i></div>
          <h4 class="addon-head">Silla de seguridad para niños</h4>
          <p class="addon-desc">Para niños de 18 a 36 kg aprox.</p>
          <div class="addon-price"><strong>$150</strong> <span>MXN / día</span></div>
          <div class="addon-qty"><button class="qty-btn minus">−</button><span class="qty">0</span><button class="qty-btn plus">+</button></div>
        </div>

        <div class="addon-card" data-id="upgrade" data-name="Upgrade de categoría" data-price="200">
          <div class="addon-icon"><i class="fa-solid fa-arrow-up"></i></div>
          <h4 class="addon-head">Upgrade de categoría</h4>
          <p class="addon-desc">Cambia a una categoría superior.</p>
          <div class="addon-price"><strong>$200</strong> <span>MXN / día</span></div>
          <div class="addon-qty"><button class="qty-btn minus">−</button><span class="qty">0</span><button class="qty-btn plus">+</button></div>
        </div>

        <div class="addon-card" data-id="gps" data-name="Dispositivo GPS" data-price="200">
          <div class="addon-icon"><i class="fa-solid fa-location-arrow"></i></div>
          <h4 class="addon-head">Dispositivo GPS</h4>
          <p class="addon-desc">Cobertura funcional en Querétaro.</p>
          <div class="addon-price"><strong>$200</strong> <span>MXN / día</span></div>
          <div class="addon-qty"><button class="qty-btn minus">−</button><span class="qty">0</span><button class="qty-btn plus">+</button></div>
        </div>

        <div class="addon-card" data-id="conductor" data-name="Conductor adicional" data-price="150">
          <div class="addon-icon"><i class="fa-solid fa-user-plus"></i></div>
          <h4 class="addon-head">Conductor adicional</h4>
          <p class="addon-desc">Agrega un conductor extra.</p>
          <div class="addon-price"><strong>$150</strong> <span>MXN / día</span></div>
          <div class="addon-qty"><button class="qty-btn minus">−</button><span class="qty">0</span><button class="qty-btn plus">+</button></div>
        </div>
      </div>

      <div class="addons-actions">
        <button class="btn btn-ghost" id="skipAddons" title="Omitir complementos">Omitir</button>
        <button class="btn btn-primary" id="toStep4">Continuar</button>
      </div>
    </section>

    <section id="step4" class="summary hidden">
      <div class="step-back"><button class="btn btn-ghost" id="backTo3">← Regresar</button></div>

      <div class="summary-grid">
        <div class="user-col" id="userCol">
          <div class="form-card">
            <h3>Tu información</h3>
            <form id="userForm" class="user-form">
              <div class="form-row grid-2">
                <div class="field">
                  <label>Entrega — Día</label>
                  <input id="ufStartDate" type="text" placeholder="dd/mm/aaaa">
                </div>
              <div class="field">
                <label>Entrega — Hora</label>
                <input id="ufStartTime" type="text" placeholder="hh:mm">
              </div>
            </div>

            <div class="form-row grid-2">
              <div class="field">
                <label>Devolución — Día</label>
                <input id="ufEndDate" type="text" placeholder="dd/mm/aaaa">
            </div>
            <div class="field">
              <label>Devolución — Hora</label>
              <input id="ufEndTime" type="text" placeholder="hh:mm">
            </div>
          </div>

              <div class="form-row">
                <div class="field">
                  <label>No. de vuelo (opcional)</label>
                  <input id="flight" placeholder="Ej. AM1234">
                </div>
              </div>

              <div class="form-row">
                <div class="field">
                  <label>Nombre completo</label>
                  <input placeholder="Tu nombre y apellidos" required>
                </div>
              </div>
              <div class="form-row grid-2">
                <div class="field">
                  <label>Móvil</label>
                  <input placeholder="55 1234 5678" required>
                </div>
                <div class="field">
                  <label>Correo electrónico</label>
                  <input type="email" placeholder="tucorreo@dominio.com" required>
                </div>
              </div>
              <div class="form-row grid-2">
                <div class="field">
                  <label>País</label>
                  <select required>
                    <option value="">Selecciona un país</option>
                    <option>México</option><option>Estados Unidos</option><option>Canadá</option>
                  </select>
                </div>
                <div class="field">
                  <label>Fecha de nacimiento</label>
                  <input id="dob" type="text" placeholder="dd/mm/aaaa" required>
                </div>
              </div>

              <label class="cbox">
                <input type="checkbox" required>
                <span class="checkmark"></span>
                <span>Estoy de acuerdo y acepto las políticas y procedimientos para la renta.</span>
              </label>

              <label class="cbox">
                <input type="checkbox">
                <span class="checkmark"></span>
                <span>Deseo recibir alertas, confirmaciones y promociones.</span>
              </label>

              <button class="btn btn-primary btn-block" id="reserveBtn">Reservar</button>
              <button type="button" class="btn btn-quote btn-block" id="quoteBtn">Cotizar</button>

              <div class="pay-logos">
                <img src="https://upload.wikimedia.org/wikipedia/commons/1/16/MercadoPago.svg" alt="MercadoPago">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/06/Oxxo_Logo.svg" alt="OXXO">
              </div>
            </form>
          </div>
        </div>

        <div class="resume-col">
          <h3>Resumen de tu reserva</h3>
          <div class="resume-card">
            <div class="resume-block">
              <div class="block-head">
                <span>Lugar y fecha</span>
                <button class="link small" id="editDates">Modificar</button>
              </div>
              <div class="block-body">
                <div class="item"><i class="fa-solid fa-box-open"></i> <strong>Entrega:</strong> <span id="sumStart">—</span></div>
                <div class="item"><i class="fa-solid fa-box"></i> <strong>Devolución:</strong> <span id="sumEnd">—</span></div>
              </div>
            </div>

            <div class="resume-block">
              <div class="block-head">
                <span>Tu Auto</span>
                <button class="link small" id="editCar">Modificar</button>
              </div>
              <div class="block-body car-sum">
                <img id="sumImg" src="" alt="Auto">
                <div class="car-meta">
                  <div id="sumName" class="car-name">—</div>
                  <div class="car-sub" id="sumCat">—</div>
                  <div class="car-icons"><i class="fa-solid fa-user-group"></i>5 <i class="fa-solid fa-suitcase-rolling"></i>3 <i class="fa-regular fa-snowflake"></i></div>
                </div>
              </div>
            </div>

            <div class="resume-block">
              <div class="block-head"><span>Extras</span></div>
              <div class="block-body" id="sumExtras">Sin complementos</div>
            </div>

            <div class="resume-block">
              <div class="block-head"><span>Detalles del precio</span></div>
              <div class="block-body">
                <div class="price-row"><span>Tarifa base</span><strong id="pBase">$0</strong></div>
                <div class="price-row"><span>Opciones de renta</span><strong id="pAddons">$0</strong></div>
                <div class="price-row"><span>Cargos e IVA</span><strong id="pTax">$0</strong></div>
                <div class="price-row total"><span>TOTAL</span><strong id="pTotal">$0</strong></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="confirmation" class="confirm hidden">
      <div class="confirm-grid">
        <div>
          <div class="big-check">✓</div>
          <h2 class="confirm-title">¡TU VIAJE ESTÁ LISTO!</h2>
          <p class="confirm-text">¡Listo! Tu reservación está confirmada. Revisa tu correo electrónico para todos los detalles y prepárate para disfrutar el camino.</p>
          <p class="confirm-text">¡Gracias por elegirnos, nos vemos pronto!</p>

          <div class="note">
            <strong>Protección limitada de responsabilidad hacia terceros (LI)</strong><br>
            Protege a terceros por daños y perjuicios ocasionados en un accidente y cubre la cantidad mínima requerida por ley.
            Tú eliges el nivel de responsabilidad sobre el auto que más vaya acorde a tus necesidades y presupuesto.
            Pregunta por nuestros relevos de responsabilidad (opcionales) al llegar al mostrador de cualquiera de nuestras oficinas.
          </div>

          <div class="receipt-card" id="receipt">
            <div class="receipt-head">
              <div>
                <div class="rc-title">Recibo de reservación</div>
                <div class="rc-sub">Conserva este comprobante como referencia</div>
              </div>
              <button class="btn btn-primary rc-print" id="printReceipt">
                <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
              </button>
            </div>

            <div class="receipt-grid">
              <div class="rc-block"><div class="rc-label">Número de reserva</div><div class="rc-value" id="rcCode">—</div></div>
              <div class="rc-block"><div class="rc-label">Método de pago</div><div class="rc-value" id="rcMethod">—</div></div>
              <div class="rc-block"><div class="rc-label">Pagado hoy</div><div class="rc-value" id="rcPaid">$0</div></div>
              <div class="rc-block"><div class="rc-label">Saldo pendiente</div><div class="rc-value" id="rcPending">$0</div></div>
              <div class="rc-block rc-wide"><div class="rc-label">Estatus</div><div class="rc-status" id="rcStatus">—</div></div>
              <div class="rc-block rc-wide"><div class="rc-label">Periodo</div><div class="rc-value" id="rcPeriod">—</div></div>
            </div>
          </div>
        </div>

        <div class="resume-final">
          <div class="rf-head"><h3>Resumen de tu reserva</h3></div>

          <div class="rf-block">
            <div class="rf-row rf-top">
              <div>
                <div class="rf-title">Lugar y fecha</div>
                <div class="rf-item"><i class="fa-solid fa-box-open"></i> <strong>Entrega:</strong> <span id="cfStart">—</span></div>
                <div class="rf-item"><i class="fa-solid fa-box"></i> <strong>Devolución:</strong> <span id="cfEnd">—</span></div>
              </div>
              <div class="rf-code">
                <div class="code-label">RESERVACIÓN</div>
                <div class="code" id="cfCode">MX-000000</div>
              </div>
            </div>
          </div>

          <div class="rf-block">
            <div class="rf-title">Tu Auto</div>
            <div class="rf-car">
              <img id="cfImg" src="" alt="Auto">
              <div>
                <div id="cfName" class="rf-car-name">—</div>
                <div id="cfCat" class="rf-car-sub">—</div>
                <div class="rf-icons"><i class="fa-solid fa-user-group"></i>5 <i class="fa-solid fa-suitcase-rolling"></i>3 <i class="fa-regular fa-snowflake"></i></div>
              </div>
            </div>
          </div>

          <div class="rf-block">
            <div class="rf-title">Extras</div>
            <div id="cfExtras" class="rf-extras">Sin complementos</div>
          </div>

          <div class="rf-block">
            <div class="rf-title">Detalles del precio</div>
            <div class="rf-price">
              <div class="price-row"><span>Tarifa base</span><strong id="cfBase">$0</strong></div>
              <div class="price-row"><span>Opciones de renta</span><strong id="cfAddons">$0</strong></div>
              <div class="price-row"><span>Cargos e IVA</span><strong id="cfTax">$0</strong></div>
              <div class="price-row total"><span>TOTAL</span><strong id="cfTotal">$0</strong></div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="quoteTemplate" class="confirm hidden" style="display:none">
      <div class="confirm-grid">
        <div>
          <div class="big-check">💼</div>
          <h2 class="confirm-title" style="background:linear-gradient(90deg,#b22222,#e05252);
        -webkit-background-clip:text;background-clip:text;color:transparent">
        COTIZACIÓN — VIAJERO
          </h2>
          <p class="confirm-text">Documento de cotización para impresión y envío.</p>

          <div class="receipt-card">
            <div class="receipt-head">
              <div>
                <div class="rc-title">Detalle de cotización</div>
                <div class="rc-sub">No. <span id="qtCode">—</span></div>
              </div>
            </div>

          <div class="receipt-grid">
            <div class="rc-block"><div class="rc-label">Lugar</div><div class="rc-value" id="qtLoc">—</div></div>
            <div class="rc-block"><div class="rc-label">Periodo</div><div class="rc-value" id="qtPeriod">—</div></div>
            <div class="rc-block"><div class="rc-label">Días</div><div class="rc-value" id="qtDays">—</div></div>
            <div class="rc-block"><div class="rc-label">Plan</div><div class="rc-value" id="qtPlan">—</div></div>
            <div class="rc-block rc-wide"><div class="rc-label">Estatus</div><div class="rc-status pending">COTIZACIÓN</div></div>
            <div class="rc-block rc-wide"><div class="rc-label">Notas</div><div class="rc-value">Precios sujetos a disponibilidad y cambios sin previo aviso.</div></div>
          </div>
        </div>
      </div>

      <div class="resume-final">
        <div class="rf-head"><h3>Resumen</h3></div>

        <div class="rf-block">
          <div class="rf-row rf-top">
            <div>
              <div class="rf-title">Lugar y fecha</div>
              <div class="rf-item"><strong>Entrega:</strong> <span id="qtStart">—</span></div>
              <div class="rf-item"><strong>Devolución:</strong> <span id="qtEnd">—</span></div>
            </div>
            <div class="rf-code">
              <div class="code-label">COTIZACIÓN</div>
              <div class="code" id="qtCode2">—</div>
            </div>
          </div>
        </div>

        <div class="rf-block">
          <div class="rf-title">Auto</div>
          <div class="rf-car">
            <img id="qtImg" src="" alt="Auto">
            <div>
              <div id="qtName" class="rf-car-name">—</div>
              <div id="qtCat" class="rf-car-sub">—</div>
           </div>
         </div>
        </div>

        <div class="rf-block">
          <div class="rf-title">Extras</div>
          <div id="qtExtras" class="rf-extras">Sin complementos</div>
        </div>

        <div class="rf-block">
          <div class="rf-title">Precio</div>
          <div class="rf-price">
            <div class="price-row"><span>Tarifa base</span><strong id="qtBase">$0</strong></div>
            <div class="price-row"><span>Opciones de renta</span><strong id="qtAddons">$0</strong></div>
            <div class="price-row"><span>Cargos e IVA</span><strong id="qtTax">$0</strong></div>
            <div class="price-row total"><span>TOTAL</span><strong id="qtTotal">$0</strong></div>
          </div>
        </div>
      </div>
    </div>
  </section>
  </main>

@section('js-vistaReservaciones')
    <script src="{{ asset('js/reservaciones.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/rangePlugin.js"></script>
    <!-- CDN para generar PDF de Cotización -->
    <script defer src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
@endsection
@endsection
