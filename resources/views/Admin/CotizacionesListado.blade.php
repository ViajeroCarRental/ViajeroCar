@extends('layouts.Ventas')
@section('Titulo', 'Cotizaciones Recientes')

@section('css-VistaCotizacionesRecientes')
    <link rel="stylesheet" href="{{ asset('css/CotizacionesListado.css') }}">
@endsection

@section('contenido-VistaCotizacionesRecientes')
<div class="main-content">
    <h1 class="titulo-seccion">Cotizaciones Recientes</h1>

    <div class="tabla-contenedor">
        <table class="tabla-cotizaciones">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Categoría</th>
                    <th>Fecha Salida</th>
                    <th>Hora Salida</th>
                    <th>Nombre Sucursal Salida</th>
                    <th>Fecha Devolución</th>
                    <th>Hora Devolución</th>
                    <th>Nombre Sucursal Devolución</th>
                    <th>Días</th>
                    <th>Tarifa Base</th>
                    <th>Tarifa Modificada</th>
                    <th>Tarifa Ajustada</th>
                    <th>Extras</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($cotizaciones as $cotizacion)
                @php
                    $cliente = json_decode($cotizacion->cliente ?? '{}');
                @endphp
                <tr>
                    <td>{{ $cotizacion->folio }}</td>
                    <td>{{ $cotizacion->categoria_nombre ?? '—' }}</td>

                    <td>{{ \Carbon\Carbon::parse($cotizacion->pickup_date)->format('d/m/Y') }}</td>
                    <td>{{ $cotizacion->pickup_time ?? '--:--' }}</td>
                    <td>{{ $cotizacion->pickup_name ?? '—' }}</td>

                    <td>{{ \Carbon\Carbon::parse($cotizacion->dropoff_date)->format('d/m/Y') }}</td>
                    <td>{{ $cotizacion->dropoff_time ?? '--:--' }}</td>
                    <td>{{ $cotizacion->dropoff_name ?? '—' }}</td>

                    <td>{{ $cotizacion->days }}</td>
                    <td>${{ number_format($cotizacion->tarifa_base, 2) }}</td>

                    <td>
                        @if($cotizacion->tarifa_modificada)
                            <span class="tarifa-editada">
                                ${{ number_format($cotizacion->tarifa_modificada, 2) }}
                            </span>
                        @else
                            —
                        @endif
                    </td>

                    <td>{{ $cotizacion->tarifa_ajustada ? 'Sí' : 'No' }}</td>
                    <td>${{ number_format($cotizacion->extras_sub, 2) }}</td>
                    <td>${{ number_format($cotizacion->iva, 2) }}</td>
                    <td class="total">${{ number_format($cotizacion->total, 2) }}</td>

                    <td>
                        {{ $cliente->nombre ?? '—' }}
                        <br>
                        <small>{{ $cliente->email ?? '' }}</small>
                    </td>

                    <td class="acciones">
                        <button class="btn-accion btn-convertir" data-id="{{ $cotizacion->id_cotizacion }}">
                            <i class="bi bi-calendar-check"></i> Crear reserva
                        </button>

                        <button class="btn-accion btn-reenviar" data-id="{{ $cotizacion->id_cotizacion }}">
                            <i class="bi bi-envelope-arrow-up"></i> Reenviar
                        </button>

                        <button class="btn-accion btn-eliminar" data-id="{{ $cotizacion->id_cotizacion }}">
                            <i class="bi bi-trash3"></i> Eliminar
                        </button>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="no-datos">No hay cotizaciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js-VistaCotizacionesRecientes')
    <script src="{{ asset('js/CotizacionesListado.js') }}"></script>
@endsection
