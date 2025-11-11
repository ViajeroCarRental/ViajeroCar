@extends('layouts.Ventas')
@section('Titulo', 'Reservaciones Activas')

@section('css-vistaReservacionesActivas')
  <link rel="stylesheet" href="{{ asset('css/reservacionesActivas.css') }}">
@endsection

@section('contenidoReservacionesActivas')
<main class="main">
  <h1 class="h1">Reservaciones activas</h1>

  <div class="toolbar">
    <input id="q" class="input" type="search" placeholder="Buscar por nombre o correo…">
    <span class="badge gray">Total <b id="count">{{ count($reservaciones) }}</b></span>
  </div>

  <!-- ======================= 📋 TABLA ======================= -->
  <section class="table">
    <div class="thead">
      <div>Código</div>
      <div>Cliente</div>
      <div>Email</div>
      <div>Número</div>
      <div>Categoría</div>
      <div>Fecha salida</div>
      <div>Fecha entrega</div>
      <div>Estado</div>
      <div>Forma de pago</div>
      <div>Total</div>
      <div>Acciones</div>
    </div>

    <div class="tbody">
      @forelse ($reservaciones as $r)
        <div class="row"
             data-codigo="{{ $r->codigo }}"
             data-cliente="{{ $r->nombre_cliente }}"
             data-email="{{ $r->email_cliente }}"
             data-numero="{{ $r->telefono_cliente }}"
             data-categoria="{{ $r->categoria }}"
             data-fecha-salida="{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}"
             data-fecha-entrega="{{ \Carbon\Carbon::parse($r->fecha_fin)->format('Y-m-d') }}"
             data-estado="{{ $r->estado }}"
             data-metodo="{{ $r->metodo_pago }}"
             data-total="{{ $r->total }}">
          <div>{{ $r->codigo }}</div>
          <div>{{ $r->nombre_cliente ?? '—' }}</div>
          <div>{{ $r->email_cliente ?? '—' }}</div>
          <div>{{ $r->telefono_cliente ?? '—' }}</div>
          <div>{{ $r->categoria ?? '—' }}</div>
          <div>{{ \Carbon\Carbon::parse($r->fecha_inicio)->format('Y-m-d') }}</div>
          <div>{{ \Carbon\Carbon::parse($r->fecha_fin)->format('Y-m-d') }}</div>
          <div>
            @php
              $estado = $r->estado;
              $color = match($estado) {
                'confirmada' => 'ok',
                'pendiente_pago' => 'warn',
                'hold' => 'gray',
                'cancelada' => 'danger',
                default => 'gray'
              };
            @endphp
            <span class="state {{ $color }}">{{ ucfirst($estado) }}</span>
          </div>
          <div>{{ ucfirst($r->metodo_pago) }}</div>
          <div>${{ number_format($r->total, 2) }} MXN</div>

          <div class="actions-wrap">
            <a href="#" class="chip">✏️ Editar</a>
            <a href="#" class="chip ghost">📄 Contrato</a>
            <form action="" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button class="iconbtn danger" type="submit">🗑️</button>
            </form>
          </div>
        </div>
      @empty
        <div class="row">
          <div colspan="10" style="text-align:center;">No hay reservaciones activas.</div>
        </div>
      @endforelse
    </div>
  </section>
</main>

<!-- ======================= 🧾 MODAL DETALLE ======================= -->
<div class="pop" id="modal">
  <div class="box">
    <header>
      <div id="mTitle">Detalle de Reservación</div>
      <button class="btn gray" id="mClose">✖</button>
    </header>

    <div class="cnt" id="mBody">
      <div class="kv"><div>Código</div><div id="mCodigo">—</div></div>
      <div class="kv"><div>Cliente</div><div id="mCliente">—</div></div>
      <div class="kv"><div>Email</div><div id="mEmail">—</div></div>
      <div class="kv"><div>Número</div><div id="mNumero">—</div></div>
      <div class="kv"><div>Categoría</div><div id="mCategoria">—</div></div>
      <div class="kv"><div>Fecha salida</div><div id="mSalida">—</div></div>
      <div class="kv"><div>Fecha entrega</div><div id="mEntrega">—</div></div>
      <div class="kv"><div>Estado</div><div id="mEstado">—</div></div>
      <div class="kv"><div>Forma de pago</div><div id="mFormaPago">—</div></div>
      <div class="kv"><div>Total</div><div id="mTotal">—</div></div>
    </div>

    <div class="actions">
      <button class="btn danger" id="mDel">Eliminar reservación</button>
      <span style="flex:1"></span>
      <button class="btn gray" id="mCancel">Cerrar</button>
      <button class="btn primary" id="mGo">CAPTURAR CONTRATO</button>
    </div>
  </div>
</div>

@endsection

@section('js-vistaReservacionesActivas')
  <script src="{{ asset('js/reservacionesActivas.js') }}"></script>
@endsection
