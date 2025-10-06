@extends('layouts.Ventas')

@section('Titulo', 'Cotizar - Viajero Car')

{{-- CSS de la vista --}}
@section('css-vistaCotizar')
<link rel="stylesheet" href="{{ asset('css/Cotizar.css') }}">
@endsection

{{-- CONTENIDO --}}
@section('contenidoCotizar')
<main class="main">
  <div class="top">
    <h1 class="h1">Nueva cotización</h1>
    <div>
      <button class="btn ghost" onclick="location.href='../dashboard.html'">Volver</button>
    </div>
  </div>

  <div class="grid">
    <section>
      <!-- PASO 1 -->
      <div class="step" data-step="1">
        <header><div class="badge">1</div><h3>PASO 1 · Viaje</h3></header>
        <div class="body">
          <div class="form-2">
            <div><label>Oficina de Pick Up</label><select id="sedePick"></select></div>
            <div><label>Oficina a Devolver</label><select id="sedeDrop"></select></div>
            <div><label>Fecha Pick Up</label><div class="fake" id="fIniBox" data-dp>yyyy-mm-dd <span class="icon">📅</span></div></div>
            <div><label>Hora Pick Up</label><div class="fake" id="hIniBox" data-tp>--:-- <span class="icon">🕑</span></div></div>
            <div><label>Fecha Devolución</label><div class="fake" id="fFinBox" data-dp>yyyy-mm-dd <span class="icon">📅</span></div></div>
            <div><label>Hora Devolución</label><div class="fake" id="hFinBox" data-tp>--:-- <span class="icon">🕑</span></div></div>
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
                <input id="precioDia" class="input" type="number" min="0" step="0.01" value="1600">
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
                <select id="moneda" class="input" style="max-width:160px">
                  <option>MXN</option><option>USD</option>
                </select>
                <span class="badge">
                  <b>TC USD</b>
                  <input type="number" id="tc" value="17" min="0.01" step="0.01"
                         style="width:90px;padding:6px 8px;border:1px solid #D0D5DD;border-radius:8px">
                </span>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:10px;justify-content:space-between">
            <button class="btn gray" id="back1" type="button">← Atrás</button>
            <button class="btn primary" id="go3" type="button">Continuar →</button>
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
            <div style="grid-column:1/-1">
              <label style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" id="smsOK"> Cliente acepta notificaciones por SMS
              </label>
            </div>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn gray" id="back2" type="button">← Atrás</button>
            <button class="btn primary" id="saveQuote" type="button">📨 Enviar cotización</button>
            <button class="btn ghost" id="saveDraft" type="button">💾 Guardar borrador</button>
            <button class="btn gray" id="btnPrint" type="button">🖨️ PDF / Imprimir</button>

            <!-- NUEVOS BOTONES -->
            <button class="btn primary" id="btnConfirm" type="button">✅ Confirmar</button>
            <button class="btn ghost" id="btnCancel" type="button">🗑️ Cancelar</button>
          </div>

          <div style="margin-top:10px">
            <div class="small" style="margin-bottom:6px">Compartir / Enviar al cliente</div>
            <div class="iconbar">
                <button class="icon-btn" id="btnPrint" title="Imprimir / PDF"><img src="../img/pdf.jpeg" alt="print"></button>
                <a class="icon-btn" id="btnMail" target="_blank" title="Correo"><img src="../img/gmail.png" alt="mail"></a>
                <a class="icon-btn" id="btnWa"   target="_blank" title="WhatsApp"><img src="../img/whatsapp.jpg" alt="wa"></a>
                <a class="icon-btn" id="btnSms"  target="_blank" title="SMS"><img src="../img/sms.png" alt="sms"></a>
            </div>
            <div class="small" style="margin-top:6px">
              Tip: si el navegador soporta Web Share API, se intentará adjuntar el PDF automáticamente.
            </div>
          </div>
        </div>
      </div>
    </section>

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
          <div style="font-weight:900">VIAJERO · Ticket de cotización</div>
          <div id="tId"></div>
        </div>
      </header>
      <div class="tbody">
        <div class="trow"><div>Cliente</div><div id="tCliente"></div></div>
        <div class="trow"><div>Contacto</div><div id="tContacto"></div></div>
        <div class="trow"><div>Pick Up</div><div id="tPick"></div></div>
        <div class="trow"><div>Devolución</div><div id="tDrop"></div></div>
        <div class="trow"><div>Vehículo</div><div id="tVeh"></div></div>
        <div class="trow"><div>Protección / Extras</div><div id="tOpts"></div></div>
        <div class="trow"><div>Total</div><div id="tTotal" style="font-weight:900"></div></div>
      </div>
    </div>
  </section>
</main>
@endsection  {{-- FIN contenido --}}

{{-- JS de la vista --}}
@section('js-vistaCotizar')
<script src="{{ asset('js/Cotizar.js') }}" defer></script>
<!-- Librerías PDF -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
@endsection
