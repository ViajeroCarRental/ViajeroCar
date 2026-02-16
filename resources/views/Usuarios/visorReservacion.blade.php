@extends('layouts.Usuarios')

@section('Titulo','Visor Reservación')

@section('contenidoReservaciones')

<div class="container py-4">

{{-- ALERTAS --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- ================= FORM ACTUALIZAR ================= --}}
<form method="POST"
      action="{{ route('visor.update',$reservacion->id_reservacion) }}"
      id="formReservacion">

@csrf
@method('PUT')

{{-- CATEGORÍA (hidden único) --}}
<input type="hidden"
       name="id_categoria"
       id="inputCategoria"
       value="{{ $reservacion->id_categoria }}">

{{-- =================================================
CARD 1 - VEHÍCULO
================================================= --}}
<div class="card mb-4 shadow">
<div class="card-header fw-bold">
    Tu Vehículo
</div>
<div class="card-body row">

<div class="col-md-4 text-center">

<img id="imgVehiculo"
     src="{{ asset(
        match($reservacion->id_categoria) {
            1 => 'img/aveo.png',
            2 => 'img/virtus.png',
            3 => 'img/jetta.png',
            4 => 'img/camry.png',
            5 => 'img/renegade.png',
            6 => 'img/avanza.png',
            7 => 'img/seltos.png',
            8 => 'img/Frontier.png',
            9 => 'img/Odyssey.png',
            10 => 'img/Urvan.png',
            11 => 'img/Tacoma.png',
            default => 'img/aveo.png'
        }
     ) }}"
     class="img-fluid rounded mb-3">


<button type="button"
        class="btn btn-outline-primary d-none mt-2"
        id="btnCambiarCategoria">
    Cambiar vehículo
</button>

</div>

<div class="col-md-8">


<table class="table table-sm">
<thead>
<tr>
    <th>Servicio</th>
    <th>Cantidad</th>
    <th>Precio</th>
    <th>Total</th>
</tr>
</thead>

<tbody>
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
               min="0"
               class="form-control editable"
               name="servicios[{{ $i }}][cantidad]"
               value="{{ $s->cantidad }}"
               readonly>
    </td>

    <td>
        <input type="number"
               step="0.01"
               min="0"
               class="form-control editable"
               name="servicios[{{ $i }}][precio]"
               value="{{ $s->precio_unitario }}"
               readonly>
    </td>

    <td>
        ${{ number_format($s->cantidad * $s->precio_unitario,2) }}
    </td>
</tr>
@endforeach
</tbody>
</table>

<hr>

<p><strong>Subtotal:</strong> ${{ number_format($subtotal,2) }}</p>
<p><strong>IVA:</strong> ${{ number_format($iva,2) }}</p>
<p><strong>Total:</strong> ${{ number_format($total,2) }}</p>

</div>
</div>
</div>

{{-- =================================================
CARD 2 - INFORMACIÓN
================================================= --}}
<div class="card mb-4 shadow">
<div class="card-header fw-bold">Tu Información</div>
<div class="card-body row g-3">

<input class="form-control editable col-md-6"
       name="nombre_cliente"
       value="{{ $reservacion->nombre_cliente }}"
       readonly>

<input class="form-control editable col-md-6"
       name="apellidos_cliente"
       value="{{ $reservacion->apellidos_cliente }}"
       readonly>

<input class="form-control editable col-md-6"
       name="email_cliente"
       value="{{ $reservacion->email_cliente }}"
       readonly>

<input class="form-control editable col-md-6"
       name="telefono_cliente"
       value="{{ $reservacion->telefono_cliente }}"
       readonly>

</div>
</div>

{{-- =================================================
CARD 3 - ITINERARIO
================================================= --}}
<div class="card mb-4 shadow">
<div class="card-header fw-bold">Tu Itinerario</div>

<div class="card-body row g-3">

<div class="col-md-6">
<label>Fecha Inicio</label>
<input type="date" class="form-control editable"
       name="fecha_inicio"
       value="{{ $reservacion->fecha_inicio }}"
       readonly>
</div>

<div class="col-md-6">
<label>Fecha Fin</label>
<input type="date" class="form-control editable"
       name="fecha_fin"
       value="{{ $reservacion->fecha_fin }}"
       readonly>
</div>

<div class="col-md-6">
<label>Hora Retiro</label>
<input type="time" class="form-control editable"
       name="hora_retiro"
       value="{{ $reservacion->hora_retiro }}"
       readonly>
</div>

<div class="col-md-6">
<label>Hora Entrega</label>
<input type="time" class="form-control editable"
       name="hora_entrega"
       value="{{ $reservacion->hora_entrega }}"
       readonly>
</div>

<div class="col-md-6">
<label>Sucursal de Retiro</label>
<select name="sucursal_retiro"
        class="form-select editable"
        disabled>
    @foreach($sucursales as $s)
    <option value="{{ $s->id_sucursal }}"
        {{ $reservacion->sucursal_retiro == $s->id_sucursal ? 'selected' : '' }}>
        {{ $s->nombre_mostrado }}
    </option>
    @endforeach
</select>
</div>

<div class="col-md-6">
<label>Sucursal de Entrega</label>
<select name="sucursal_entrega"
        class="form-select editable"
        disabled>
    @foreach($sucursales as $s)
    <option value="{{ $s->id_sucursal }}"
        {{ $reservacion->sucursal_entrega == $s->id_sucursal ? 'selected' : '' }}>
        {{ $s->nombre_mostrado }}
    </option>
    @endforeach
</select>
</div>

</div>
</div>

{{-- BOTONES --}}
<div class="d-flex gap-3">
<button type="button" class="btn btn-warning" id="btnEditar">Editar</button>
<button type="submit" class="btn btn-primary d-none" id="btnActualizar">Actualizar</button>
</div>

</form>

{{-- ================= FORM ELIMINAR ================= --}}
<form method="POST"
      action="{{ route('visor.delete',$reservacion->id_reservacion) }}"
      class="mt-3"
      onsubmit="return confirm('¿Seguro que deseas eliminar la reservación?')">

@csrf
@method('DELETE')

<button type="submit" class="btn btn-danger">Eliminar</button>
<button type="button" class="btn btn-danger">Mario</button>
</form>

</div>
@endsection

{{-- ================= JS ================= --}}
@section('js-vistaReservaciones')
<script src="{{ asset('js/visorReservacion.js') }}"></script>
@endsection

{{-- ================= MODAL VEHÍCULO ================= --}}
<div class="modal fade" id="modalCategoria">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Selecciona un vehículo</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body row">
@php
$vehiculos = [
['id'=>1,'txt'=>'C | Compacto','img'=>'aveo.png'],
['id'=>2,'txt'=>'D | Medianos','img'=>'virtus.png'],
['id'=>3,'txt'=>'E | Grandes','img'=>'jetta.png'],
['id'=>4,'txt'=>'F | Full Size','img'=>'camry.png'],
['id'=>5,'txt'=>'IC | SUV Completa','img'=>'renegade.png'],
['id'=>6,'txt'=>'I | SUV Mediana','img'=>'avanza.png'],
];
@endphp

@foreach($vehiculos as $v)
<div class="col-md-4 text-center mb-3">
<img src="{{ asset('img/'.$v['img']) }}" class="img-fluid">
<p>{{ $v['txt'] }}</p>
<button type="button"
class="btn btn-primary elegirCategoria"
data-id="{{ $v['id'] }}"
data-txt="{{ $v['txt'] }}"
data-img="{{ asset('img/'.$v['img']) }}">
Elegir
</button>
</div>
@endforeach

</div>
</div>
</div>
</div>
