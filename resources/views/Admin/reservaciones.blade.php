@extends('layouts.Ventas')
@section('Titulo', 'reservacionesAdmin')
    @section('css-vistaHomeVentas')
        <link rel="stylesheet" href="{{ asset('css/reservacionesAdmin.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Cabin:wght@400;600;700&display=swap" rel="stylesheet">
    @endsection
@section('contenidoreservacionesAdmin')
    <div class="wrap">


  <main class="main">
    <div class="top">
      <button class="btn ghost burger" id="burger">☰</button>
      <h1 class="h1">Nueva reservación</h1>
      <div><button class="btn ghost" onclick="location.href='../dashboard.html'">Salir</button></div>
    </div>

    <div class="grid">
      <section>
        <!-- PASO 1 -->
        <div class="step" data-step="1">
          <header><div class="badge">1</div><h3>PASO 1 · Viaje</h3></header>
          <div class="body">
            <div class="form-2">
              <div><label>Origen</label><select id="sedePick"></select></div>
              <div><label>Destino</label><select id="sedeDrop"></select></div>

              <div><label>Fecha de salida</label><div class="fake" id="fIniBox" data-dp>YYYY-MM-DD <span class="icon">📅</span></div></div>
              <div><label>Hora de salida</label><div class="fake" id="hIniBox" data-tp>--:-- AM/PM <span class="icon">🕑</span></div></div>

              <div><label>Fecha de llegada</label><div class="fake" id="fFinBox" data-dp>YYYY-MM-DD <span class="icon">📅</span></div></div>
              <div><label>Hora de llegada</label><div class="fake" id="hFinBox" data-tp>--:-- AM/PM <span class="icon">🕑</span></div></div>
            </div>

            <div class="form-2" style="align-items:center">
              <div style="grid-column:1/-1;display:flex;gap:10px;align-items:center">
                <button class="btn primary" type="button" id="btnVeh">🚗 Seleccionar vehículo</button>
                <input id="vehiculoSel" class="input" type="text" placeholder="Vehículo seleccionado" readonly>
                <span id="diasBadge" class="badge" style="background:#ECFDF3;border:1px solid #ABEFC6;color:var(--ok)">0 día(s)</span>
              </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
              <button class="btn primary" id="go2" type="button">Continuar →</button>
            </div>
          </div>
        </div>

        <!-- PASO 2 -->
        <div class="step" data-step="2" style="margin-top:16px;display:none">
          <header><div class="badge">2</div><h3>PASO 2 · Vehículo y opciones</h3></header>
          <div class="body">
            <div class="form-2">
              <div>
                <label>Protecciones</label>
                <select id="proteccion">
                  <option value="">Selecciona</option>
                  <optgroup label="PARA LA UNIDAD">
                    <option value="LDW|675">LDW 100% sin deducible ($675/día)</option>
                    <option value="CDW20|375">CDW 20% ($375/día)</option>
                    <option value="CDW10|450">CDW 10% ($450/día)</option>
                    <option value="PDW|575">PDW ($575/día)</option>
                  </optgroup>
                  <optgroup label="RESPONSABILIDAD CIVIL">
                    <option value="LI|0">LI incluido</option>
                  </optgroup>
                </select>
              </div>
              <div>
                <label>Costo vehículo (por día)</label>
                <div style="display:flex;gap:10px">
                  <input id="precioDia" class="input" type="number" min="0" step="0.01" value="1868">
                  <button class="btn gray" id="recalc" type="button">Calcular</button>
                </div>
                <div class="small">24 h por día + 1 h de cortesía.</div>
              </div>
            </div>

            <h4 style="margin:10px 0 0">Adicionales</h4>
            <div id="addGrid" class="add-grid"></div>

            <div class="form-2" style="margin-top:6px">
              <div>
                <label>Moneda</label>
                <div style="display:flex;gap:10px;align-items:center">
                  <select id="moneda" class="input" style="max-width:160px"><option>MXN</option><option>USD</option></select>
                  <span class="badge"><b>TC USD</b> <input type="number" id="tc" value="17" min="0.01" step="0.01" style="width:90px;padding:6px 8px;border:1px solid #D0D5DD;border-radius:8px"></span>
                </div>
              </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:space-between">
              <button class="btn gray" id="back1" type="button">← Atrás</button>
              <button class="btn primary" id="go3"  type="button">Continuar →</button>
            </div>
          </div>
        </div>

        <!-- PASO 3 -->
        <div class="step" data-step="3" style="margin-top:16px;display:none">
          <header><div class="badge">3</div><h3>PASO 3 · Cliente y envío</h3></header>
          <div class="body">
            <div class="form-2">
              <div><label>Nombre</label><input id="cNombre" class="input" type="text"></div>
              <div><label>Apellido(s)</label><input id="cApellidos" class="input" type="text"></div>
              <div><label>Email</label><input id="cEmail" class="input" type="text"></div>
              <div><label>Teléfono</label><input id="cTel" class="input" type="text" placeholder="+52..."></div>
              <div><label>País</label><input id="cPais" class="input" type="text" value="MÉXICO"></div>
              <div><label>Vuelo</label><input id="cVuelo" class="input" type="text" placeholder="UA2068"></div>
              <div style="grid-column:1/-1"><label style="display:flex;align-items:center;gap:8px"><input type="checkbox" id="smsOK"> Cliente acepta notificaciones por SMS</label></div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <button class="btn gray" id="back2" type="button">← Atrás</button>
              <button class="btn primary" id="saveAll" type="button">✅ Registrar reservación</button>
              <button class="btn ghost"   id="saveDraft" type="button">💾 Guardar borrador</button>
            </div>

            <div style="margin-top:10px">
              <div class="small" style="margin-bottom:6px">Compartir / Enviar al cliente</div>
              <div class="iconbar">
                <button class="icon-btn" id="btnPrint" title="Imprimir / PDF"><img src="../assets/media/pdf.jpeg" alt="print"></button>
                <a class="icon-btn" id="btnMail" target="_blank" title="Correo"><img src="../assets/media/gmail.png" alt="mail"></a>
                <a class="icon-btn" id="btnWa"   target="_blank" title="WhatsApp"><img src="../assets/media/whatsapp.jpg" alt="wa"></a>
                <a class="icon-btn" id="btnSms"  target="_blank" title="SMS"><img src="../assets/media/sms.png" alt="sms"></a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== Resumen ===== -->
      <aside class="sticky">
        <div class="card">
          <div class="head">Resumen de cotización</div>
          <div class="cnt">
            <div class="row"><div>Tarifa Base</div><div class="small" id="baseLine">—</div></div>
            <div class="row"><div>Protección</div><div class="small" id="proteName">—</div></div>
            <div class="row"><div>Adicionales</div><div class="small" id="extrasName">—</div></div>
            <div class="row"><div>Subtotal</div><div id="subTot">$0</div></div>
            <div class="row"><div>IVA (16%)</div><div id="iva">$0</div></div>
            <div class="row"><div style="font-weight:900">Total</div><div class="total" id="total">$0</div></div>
          </div>
        </div>
      </aside>
    </div>

    <!-- Ticket -->
    <section id="ticket">
      <div class="ticket" id="ticketCard">
        <header>
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:900">VIAJERO · Ticket de reservación</div>
            <div id="tId"></div>
          </div>
        </header>

        <div class="tbody">
          <div class="trow"><div class="tlabel">Estatus</div><div class="tval" id="tStatus">—</div></div>
          <div class="trow"><div class="tlabel">Creada</div><div class="tval" id="tCreated">—</div></div>
          <div class="hr"></div>

          <div class="trow"><div class="tlabel">Cliente</div><div class="tval" id="tCliente">—</div></div>
          <div class="trow"><div class="tlabel">Contacto</div><div class="tval" id="tContacto">—</div></div>
          <div class="trow"><div class="tlabel">País</div><div class="tval" id="tPais">—</div></div>
          <div class="trow"><div class="tlabel">Vuelo</div><div class="tval" id="tVuelo">—</div></div>
          <div class="trow"><div class="tlabel">SMS</div><div class="tval" id="tSms">—</div></div>

          <div class="hr"></div>

          <div class="trow"><div class="tlabel">Pick Up</div><div class="tval" id="tPick">—</div></div>
          <div class="trow"><div class="tlabel">Devolución</div><div class="tval" id="tDrop">—</div></div>
          <div class="trow"><div class="tlabel">Vehículo</div><div class="tval" id="tVeh">—</div></div>
          <div class="trow"><div class="tlabel">Días</div><div class="tval" id="tDias">—</div></div>

          <div class="hr"></div>

          <div class="trow"><div class="tlabel">Tarifa por día</div><div class="tval" id="tRate">—</div></div>
          <div class="trow"><div class="tlabel">Protección</div><div class="tval" id="tProt">—</div></div>
          <div class="trow"><div class="tlabel">Extras</div><div class="tval" id="tExtras">—</div></div>
          <div class="trow"><div class="tlabel">Moneda</div><div class="tval" id="tMoneda">—</div></div>

          <div class="hr"></div>

          <div class="trow"><div class="tlabel">Subtotal</div><div class="tval" id="tSubtotal">—</div></div>
          <div class="trow"><div class="tlabel">IVA (16%)</div><div class="tval" id="tIva">—</div></div>
          <div class="trow"><div class="tlabel">Total</div><div class="tval totline" id="tTotal">—</div></div>

          <div class="tnote">* Los precios incluyen impuestos salvo indicación en contrario. Horarios en formato 12 h. 24 h por día + 1 h de cortesía.</div>
        </div>
      </div>
    </section>
  </main>
</div>

<!-- ====== Modales ====== -->
<!-- Date Picker (rango con arrastre) -->
<div class="pop" id="dpPop">
  <div class="box" style="width:min(360px,96vw)">
    <div class="dp-head">
      <button class="btn gray" id="dpPrev">◀</button>
      <div class="dp-title" id="dpMonth">—</div>
      <button class="btn gray" id="dpNext">▶</button>
    </div>
    <div class="grid7" id="dpGrid"></div>
    <footer>
      <button class="btn gray" id="dpToday">Hoy</button>
      <button class="btn gray" id="dpClear">Limpiar</button>
      <button class="btn primary" id="dpApply">Aplicar</button>
    </footer>
  </div>
</div>

<!-- Time Picker -->
<div class="pop" id="tpPop">
  <div class="box tpbox">
    <div class="tp-head">
      <div class="tp-title">Selecciona hora</div>
      <div style="display:flex;gap:8px;align-items:center">
        <button class="btn gray" id="tpAM" type="button">AM</button>
        <button class="btn gray" id="tpPM" type="button">PM</button>
        <button class="btn gray" id="tpClose">✖</button>
      </div>
    </div>
    <div class="tp-wrap">
      <div class="tp-col">
        <h5>Hora (12 h)</h5>
        <div class="tp-hours" id="tpHours"></div>
        <div class="tp-input">
          <label class="small" for="tpManual" style="min-width:90px">Escribir</label>
          <input id="tpManual" type="text" inputmode="numeric" placeholder="hh:mm AM/PM">
          <button class="btn gray" id="tpSetManual">Aplicar</button>
        </div>
      </div>
      <div class="tp-col">
        <h5>Minutos</h5>
        <div class="tp-mins" id="tpMins"></div>
        <div class="tp-quick">
          <span class="tp-q" data-q="07:00 AM">Mañana 07:00 AM</span>
          <span class="tp-q" data-q="09:00 AM">Mañana 09:00 AM</span>
          <span class="tp-q" data-q="12:00 PM">Mediodía 12:00 PM</span>
          <span class="tp-q" data-q="04:00 PM">Tarde 04:00 PM</span>
          <span class="tp-q" data-q="08:00 PM">Noche 08:00 PM</span>
          <span class="tp-q" data-q="+30">+30 min</span>
          <span class="tp-q" data-q="now">Ahora</span>
        </div>
      </div>
    </div>
    <footer>
      <button class="btn gray" id="tpClear">Limpiar</button>
      <button class="btn primary" id="tpApply">Aplicar</button>
    </footer>
  </div>
</div>

<div class="pop" id="vehPop">
  <div class="box vbox">
    <header><span>Vehículos disponibles</span><button class="btn gray" id="vehClose">✖</button></header>
    <div id="vehList" style="padding:12px"></div>
  </div>
</div>

@section('js-vistareservacionesAdmin')
        <script src="{{ asset('js/reservacionesAdmin.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
@endsection
@endsection
