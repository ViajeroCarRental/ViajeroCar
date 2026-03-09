<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContratoBaseController extends Controller
{
    public function resumenContrato($idReservacion)
    {
        try {
            $res = DB::table('reservaciones as r')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria', '=', 'cck.id_categoria')
                ->where('r.id_reservacion', $idReservacion)
                ->select(
                    'r.codigo',
                    'r.nombre_cliente',
                    'r.telefono_cliente',
                    'r.email_cliente',
                    'v.marca as veh_marca',
                    'v.modelo as veh_modelo',
                    'v.transmision as veh_transmision',
                    'v.asientos as veh_asientos',
                    'v.puertas as veh_puertas',
                    'v.kilometraje as veh_km',
                    'v.categoria as veh_categoria',
                    'cck.costo_km as precio_km_dropoff',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.hora_entrega',
                    'r.tarifa_base',
                    'r.tarifa_modificada',
                    'r.delivery_activo',
                    'r.delivery_km',
                    'r.delivery_total'
                )
                ->first();

            if (!$res) {
                return response()->json(['success' => false, 'msg' => 'Reservación no encontrada']);
            }

            $dias = max(1, \Carbon\Carbon::parse($res->fecha_inicio)->diffInDays(\Carbon\Carbon::parse($res->fecha_fin)));

            // 1. Cálculo de Renta Base
            $precioPorDia = $res->tarifa_modificada ?? $res->tarifa_base;
            $montoRentaBase = $precioPorDia * $dias;

            // 2. Seguros (Cálculo corregido)
            $seguros = ['tipo' => null, 'lista' => [], 'total' => 0];
            $paquete = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
                ->where('rps.id_reservacion', $idReservacion)
                ->select('sp.nombre', 'sp.descripcion', 'rps.precio_por_dia')
                ->first();

            if ($paquete) {
                $seguros['tipo'] = 'paquete';
                $seguros['lista'][] = ['nombre' => $paquete->nombre, 'precio' => $paquete->precio_por_dia];
                $seguros['total'] = $paquete->precio_por_dia * $dias;
            } else {
                $individuales = DB::table('reservacion_seguro_individual as ri')
                    ->join('seguro_individuales as si', 'ri.id_individual', '=', 'si.id_individual')
                    ->where('ri.id_reservacion', $idReservacion)
                    ->select('si.nombre', 'ri.precio_por_dia')
                    ->get();
                if ($individuales->count() > 0) {
                    $seguros['tipo'] = 'individuales';
                    foreach ($individuales as $ind) {
                        $seguros['lista'][] = ['nombre' => $ind->nombre, 'precio' => $ind->precio_por_dia];
                        $seguros['total'] += ($ind->precio_por_dia * $dias);
                    }
                }
            }

            // 3. Servicios adicionales (Cálculo corregido)
            $serviciosData = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->where('rs.id_reservacion', $idReservacion)
                ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario')
                ->get();

            $listaServicios = [];
            $totalServiciosAdic = 0;
            foreach ($serviciosData as $srv) {
                $sub = ($srv->cantidad * $srv->precio_unitario) * $dias;
                $totalServiciosAdic += $sub;
                $listaServicios[] = ['nombre' => $srv->nombre, 'cantidad' => $srv->cantidad, 'precio' => $srv->precio_unitario, 'total' => $sub];
            }

            // 4. Cargos Extras (Delivery)
            $cargos = [];
            $totalExtras = 0;
            if ($res->delivery_activo) {
                $totalExtras += $res->delivery_total;
                $cargos[] = ['nombre' => 'Entrega a domicilio', 'km' => $res->delivery_km, 'total' => $res->delivery_total];
            }

            // 5. MATEMÁTICA FINAL (Aquí estaba el error)
            // Sumamos TODO: Renta + Seguros + Servicios + Delivery
            $nuevoSubtotal = $montoRentaBase + $seguros['total'] + $totalServiciosAdic + $totalExtras;
            $nuevoIva = $nuevoSubtotal * 0.16;
            $nuevoTotal = $nuevoSubtotal + $nuevoIva;

            $totales = [
                'tarifa_base'      => $res->tarifa_base,
                'tarifa_modificada' => $res->tarifa_modificada,
                'subtotal'         => $nuevoSubtotal,
                'iva'              => $nuevoIva,
                'total'            => $nuevoTotal
            ];

            // 6. Pagos
            $pagosRealizados = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->where('estatus', 'paid')
                ->sum('monto');

            return response()->json([
                'success' => true,
                'data' => [
                    'codigo'   => $res->codigo,
                    'cliente'  => ['nombre' => $res->nombre_cliente, 'telefono' => $res->telefono_cliente, 'email' => $res->email_cliente],
                    'vehiculo' => ['marca' => $res->veh_marca, 'modelo' => $res->veh_modelo, 'categoria' => $res->veh_categoria, 'transmision' => $res->veh_transmision, 'km' => $res->veh_km, 'precio_km_dropoff' => $res->precio_km_dropoff],
                    'fechas'   => ['inicio' => $res->fecha_inicio, 'fin' => $res->fecha_fin, 'dias' => $dias],
                    'seguros'  => $seguros,
                    'servicios' => $listaServicios,
                    'cargos'   => $cargos,
                    'totales'  => $totales,
                    'pagos'    => ['realizados' => $pagosRealizados, 'saldo' => max(0, $nuevoTotal - $pagosRealizados)]
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR resumenContrato: " . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Error interno'], 500);
        }
    }

    protected function calcularTotalProtecciones($idReservacion)
    {
        $dias = DB::table('reservaciones')
            ->selectRaw("DATEDIFF(fecha_fin, fecha_inicio)   as dias")
            ->where('id_reservacion', $idReservacion)
            ->value('dias') ?? 1;


        // Paquete
        $paquete = DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if ($paquete) {
            return $paquete->precio_por_dia * $dias;
        }

        // Individuales
        return DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $idReservacion)
            ->sum(DB::raw("precio_por_dia * {$dias}"));
    }

    protected function obtenerIndividualesSeleccionados($idReservacion)
    {
        return DB::table('reservacion_seguro_individual as rsi')
            ->join('seguro_individuales as si', 'rsi.id_individual', '=', 'si.id_individual')
            ->select(
                'si.id_individual',
                'si.nombre',
                'si.descripcion',
                'rsi.precio_por_dia'
            )
            ->where('rsi.id_reservacion', $idReservacion)
            ->get();
    }
}
