@extends('layouts.Usuarios')

@section('Titulo','Visor Reservación')

@section('contenidoReservaciones')

<div class="container py-4">

{{-- ================= ALERTAS ================= --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


{{-- =================================================
CARD 1 – VEHÍCULO / CATEGORÍA / SERVICIOS
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
@csrf
@method('PUT')

<input type="hidden" name="card" value="card1">

<input type="hidden"
       name="id_categoria"
       id="inputCategoria"
       value="{{ $reservacion->id_categoria }}">

<div class="card mb-4 shadow">
<div class="card-header fw-bold d-flex justify-content-between align-items-center">
    Vehículo y servicios
    <div>
        <button type="button"
                class="btn btn-outline-warning btn-sm"
                id="btnEditarServicios">
            Editar
        </button>

        <button type="submit"
                class="btn btn-primary btn-sm d-none"
                id="btnGuardarCard1">
            Guardar
        </button>
    </div>
</div>

<div class="card-body row">

{{-- IZQUIERDA --}}
<div class="col-md-4 text-center">

<img id="imgVehiculo"
     class="img-fluid rounded mb-3"
     src="{{ asset(
        match($reservacion->id_categoria) {
            1 => 'img/aveo.png',
            2 => 'img/virtus.png',
            3 => 'img/jetta.png',
            4 => 'img/camry.png',
            5 => 'img/renegade.png',
            6 => 'img/avanza.png',
            7 => 'img/Frontier.png',
            8 => 'img/Frontier.png',
            9 => 'img/Frontier.png',
            default => ''
        }
     ) }}">

<h6 class="fw-bold" id="textoCategoria">
    {{
        match($reservacion->id_categoria) {
            1 => 'C | Compacto',
            2 => 'D | Mediano',
            3 => 'E | Grande',
            4 => 'F | Full Size',
            5 => 'IC | SUV Compacta',
            6 => 'I | SUV Mediana',
            7 => 'IB | SUV Familiar Compacta',
            8 => 'M | Minivan',
            9 => 'L | Van Pasajeros 13',
            10 => 'H | Pickup Doble Cabina',
            11 => 'HI | Pickup Doble Cabina',
            default => 'Sin categoría'
        }
    }}
</h6>

<button type="button"
        class="btn btn-outline-primary btn-sm d-none mt-2"
        id="btnCambiarCategoria">
    Cambiar categoría
</button>

</div>

{{-- DERECHA --}}
<div class="col-md-8">

{{-- SELECT SERVICIOS --}}
<div class="row mb-3 d-none" id="contenedorAgregarServicio">
    <div class="col-md-8">
        <select id="selectServicio" class="form-select">
            <option value="">-- Selecciona un servicio --</option>
            @foreach($catalogoServicios as $servicio)
                <option value="{{ $servicio->id_servicio }}"
                        data-nombre="{{ $servicio->nombre }}"
                        data-precio="{{ $servicio->precio }}">
                    {{ $servicio->nombre }} (${{ number_format($servicio->precio,2) }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <button type="button"
                class="btn btn-success w-100"
                id="btnConfirmarAgregar">
            Agregar
        </button>
    </div>
</div>

<table class="table table-sm">
<thead>
<tr>
    <th>Servicio</th>
    <th>Cantidad</th>
    <th>Precio</th>
    <th>Total</th>
    <th></th>
</tr>
</thead>

<tbody id="tablaServicios">
@foreach($servicios as $i => $s)
<tr>
    <td>
        {{ $s->nombre }}
        <input type="hidden"
               name="servicios[{{ $i }}][id]"
               value="{{ $s->id_servicio }}">
    </td>

    <td>
        <input type="number"
               min="1"
               name="servicios[{{ $i }}][cantidad]"
               class="form-control editable-servicio"
               value="{{ $s->cantidad }}"
               readonly>
    </td>

    <td>
        <input type="number"
               step="0.01"
               min="0"
               name="servicios[{{ $i }}][precio]"
               class="form-control editable-servicio"
               value="{{ $s->precio_unitario }}"
               readonly>
    </td>

    <td>${{ number_format($s->cantidad * $s->precio_unitario,2) }}</td>

    <td>
        <button type="button"
                class="btn btn-sm btn-danger d-none btnEliminarServicio">✖</button>
    </td>
</tr>
@endforeach
</tbody>
</table>

<p><strong>Subtotal:</strong> ${{ number_format($subtotal,2) }}</p>
<p><strong>IVA:</strong> ${{ number_format($iva,2) }}</p>
<p><strong>Total:</strong> ${{ number_format($total,2) }}</p>

</div>
</div>
</div>
</form>


{{-- =================================================
CARD 2 – DATOS DEL CLIENTE
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
@csrf
@method('PUT')

<input type="hidden" name="card" value="card2">

<div class="card mb-4 shadow">
<div class="card-header fw-bold d-flex justify-content-between align-items-center">
    Datos del cliente
    <div>
        <button type="button"
                class="btn btn-outline-warning btn-sm"
                id="btnEditarCliente">
            Editar
        </button>

        <button type="submit"
                class="btn btn-primary btn-sm d-none"
                id="btnGuardarCliente">
            Actualizar
        </button>
    </div>
</div>

<div class="card-body">

<input class="form-control mb-2 editable-cliente"
       name="nombre_cliente"
       value="{{ $cliente->nombre_cliente }}"
       readonly required>

<input class="form-control mb-2 editable-cliente"
       name="apellidos_cliente"
       value="{{ $cliente->apellidos_cliente }}"
       readonly required>

<input class="form-control mb-2 editable-cliente"
       type="email"
       name="email_cliente"
       value="{{ $cliente->email_cliente }}"
       readonly required>

<input class="form-control mb-2 editable-cliente"
       name="telefono_cliente"
       value="{{ $cliente->telefono_cliente }}"
       readonly required>

</div>
</div>
</form>


{{-- =================================================
CARD 3 – ITINERARIO
================================================= --}}
<form method="POST"
      action="{{ route('visor.update', $reservacion->id_reservacion) }}">
@csrf
@method('PUT')

<input type="hidden" name="card" value="card3">

<div class="card mb-4 shadow">
<div class="card-header fw-bold d-flex justify-content-between align-items-center">
    Itinerario
    <div>
        <button type="button"
                class="btn btn-outline-warning btn-sm"
                id="btnEditarItinerario">
            Editar
        </button>

        <button type="submit"
                class="btn btn-primary btn-sm d-none"
                id="btnGuardarItinerario">
            Actualizar
        </button>
    </div>
</div>

<div class="card-body">

    <div class="alert alert-danger d-none" id="alertCard3"></div>

<label>Fecha retiro</label>
<input type="date"
       class="form-control mb-2 editable-itinerario"
       name="fecha_inicio"
       value="{{ $itinerario->fecha_inicio }}"
       readonly required>

<input type="date"
       class="form-control mb-2 editable-itinerario"
       name="fecha_fin"
       value="{{ $itinerario->fecha_fin }}"
       readonly required>

<input type="time"
       class="form-control mb-2 editable-itinerario"
       name="hora_retiro"
       value="{{ $itinerario->hora_retiro }}"
       readonly required>

<input type="time"
       class="form-control mb-2 editable-itinerario"
       name="hora_entrega"
       value="{{ $itinerario->hora_entrega }}"
       readonly required>

<select name="sucursal_retiro"
        class="form-select mb-2 editable-itinerario bloqueado">
@foreach($sucursales as $s)
    <option value="{{ $s->id_sucursal }}"
        @selected($itinerario->sucursal_retiro == $s->id_sucursal)>
        {{ $s->nombre_mostrado }}
    </option>
@endforeach
</select>

<select name="sucursal_entrega"
        class="form-select mb-3 editable-itinerario bloqueado">
@foreach($sucursales as $s)
    <option value="{{ $s->id_sucursal }}"
        @selected($itinerario->sucursal_entrega == $s->id_sucursal)>
        {{ $s->nombre_mostrado }}
    </option>
@endforeach
</select>

</div>
</div>
</form>

</div>




{{-- ================= MODAL CATEGORÍA ================= --}}
<div class="modal fade" id="modalCategoria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Selecciona categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="1">
          Compacto
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="2">
          Mediano
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="3">
          Grandes
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="4">
          Full Size
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="5">
          SUV Compacta
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="6">
          SUV mediana
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="7">
          SUV familiar Compacta
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="8">
          Minivan
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="9">
        Van Pasajeros 13
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="10">
        Pickup Doble Cabina
        </button>

        <button type="button"
                class="btn btn-outline-primary elegirCategoria"
                data-id="11">
        Pickup 4x4 Doble Cabina
        </button>


      </div>
    </div>
  </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.bloqueado {
    pointer-events: none;
    background-color: #e9ecef;
    opacity: 1;
}
</style>



@endsection

<script src="{{ asset('js/visorReservacion.js') }}"></script>

