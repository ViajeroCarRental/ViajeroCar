@extends('layouts.Flotillas')
@section('Titulo', 'Flotilla')

@section('css-vistaFlotilla')
<link rel="stylesheet" href="{{ asset('css/flotilla.css') }}">
@endsection

@section('contenidoMantenimiento')
<main>
  <div class="topbar">
    <div><strong>Autos · Flotilla</strong></div>
  </div>

  <div class="content">
    <h1 class="title">Flotilla</h1>
    <p class="sub">Inventario y disponibilidad actual.</p>

    <div style="overflow:auto">
      <table class="table" id="tblFleet">
        <thead>
          <tr>
            <th>Modelo</th>
            <th>Marca</th>
            <th>Año</th>
            <th>Color</th>
            <th>Placa</th>
            <th>Número de Serie</th>
            <th>Categoría</th>
            <th>Kilometraje</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vehiculos as $v)
            <tr>
              <td>{{ $v->modelo }}</td>
              <td>{{ $v->marca }}</td>
              <td>{{ $v->anio }}</td>
              <td>{{ $v->color }}</td>
              <td>{{ $v->placa }}</td>
              <td>{{ $v->numero_serie }}</td>
              <td>{{ $v->categoria }}</td>
              <td>{{ number_format($v->kilometraje) }} km</td>
              <td>{{ $v->estatus ?? 'Disponible' }}</td>
              <td>
                <form action="{{ route('flotilla.editar', $v->id_vehiculo) }}" method="POST" style="display:inline">
                  @csrf
                  <button type="submit" class="btn btn-sm" title="Editar">✏️</button>
                </form>

                <form action="{{ route('flotilla.eliminar', $v->id_vehiculo) }}" method="POST" style="display:inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este vehículo?')" title="Eliminar">🗑️</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" style="text-align:center;">No hay vehículos registrados</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

@section('js-vistaFlotilla')
<script src="{{ asset('js/flotilla.js') }}"></script>
@endsection
@endsection
