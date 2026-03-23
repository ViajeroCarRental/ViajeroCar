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
                ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria', '=', 'cck.id_categoria')
                ->where('r.id_reservacion', $idReservacion)
                ->select(
                    'r.*',
                    'c.numero_contrato',
                    'c.id_contrato as contrato_id',
                    'v.id_vehiculo as veh_id',
                    'v.marca as veh_marca',
                    'v.modelo as veh_modelo',
                    'v.transmision as veh_transmision',
                    'v.asientos as veh_asientos',
                    'v.puertas as veh_puertas',
                    'v.kilometraje as veh_km',
                    'v.categoria as veh_categoria',
                    'v.placa as veh_placa',
                    'cck.costo_km as precio_km_dropoff'
                )->first();

            if (!$res) {
                return response()->json(['success' => false, 'msg' => 'Reservación no encontrada'], 404);
            }

            $fechaInicio = \Carbon\Carbon::parse($res->fecha_inicio);
            $fechaFin = \Carbon\Carbon::parse($res->fecha_fin);
            $dias = max(1, $fechaInicio->diffInDays($fechaFin));

            $seguros = ['tipo' => null, 'lista' => [], 'total' => 0];
            $listaServicios = [];
            $listaCargos = [];
            $totalServiciosAdic = 0;
            $totalCargosExtra = 0; // Para delivery
            $totalCargosPaso4 = 0; // Para Gasolina, Sillas, etc.
            $entregaInfo = null;


            // if ($res->delivery_activo && $res->delivery_total > 0) {
            //     $totalCargosPaso4 += (float) $res->delivery_total;
            //     $listaCargos[] = [
            //         'nombre'   => '🚚 Servicio de Dropoff',
            //         'cantidad' => 1,
            //         'total'    => (float) $res->delivery_total
            //     ];

            //     $entregaInfo = [
            //         'tipo'      => 'A domicilio',
            //         'direccion' => $res->delivery_direccion ?? '—',
            //         'monto'     => (float) $res->delivery_total,
            //         'total'     => (float) $res->delivery_total,
            //     ];
            // }

            // Cálculo de Seguros (Paquete o Individuales)
            $paquete = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
                ->where('rps.id_reservacion', $idReservacion)
                ->select('sp.nombre', 'rps.precio_por_dia')->first();

            if ($paquete) {
                $seguros['tipo'] = 'paquete';
                $seguros['lista'][] = ['nombre' => $paquete->nombre, 'precio' => $paquete->precio_por_dia];
                $seguros['total'] = $paquete->precio_por_dia * $dias;
            } else {
                $individuales = DB::table('reservacion_seguro_individual as ri')
                    ->join('seguro_individuales as si', 'ri.id_individual', '=', 'si.id_individual')
                    ->where('ri.id_reservacion', $idReservacion)
                    ->select('si.nombre', 'ri.precio_por_dia')->get();
                foreach ($individuales as $ind) {
                    $seguros['lista'][] = ['nombre' => $ind->nombre, 'precio' => $ind->precio_por_dia];
                    $seguros['total'] += ($ind->precio_por_dia * $dias);
                }
            }

            // Cálculo de Servicios Adicionales (Paso 2)
            $serviciosDB = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->where('rs.id_reservacion', $idReservacion)
                ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario')->get();

            foreach ($serviciosDB as $srv) {
                $sub = ($srv->cantidad * $srv->precio_unitario) * $dias;
                $totalServiciosAdic += $sub;
                $listaServicios[] = [
                    'nombre' => $srv->nombre,
                    'cantidad' => $srv->cantidad,
                    'total' => $sub
                ];
            }

            // Lógica de Delivery
            if ($res->delivery_activo) {
                $totalCargosExtra = $res->delivery_total;

                $listaServicios[] = [
                    'nombre' => 'Servicio de Delivery',
                    'cantidad' => 1,
                    'total' => $res->delivery_total
                ];

                $entregaInfo = [
                    'tipo' => 'A domicilio',
                    'direccion' => $res->delivery_direccion ?? 'Dirección no especificada'
                ];
            }

            $contratoLigado = DB::table('contratos')->where('id_reservacion', $idReservacion)->first();

            if ($contratoLigado) {
                $cargosExtrasDB = DB::table('cargo_adicional')
                    ->where('id_contrato', $contratoLigado->id_contrato)
                    ->get();

                foreach ($cargosExtrasDB as $cargo) {
                    $totalCargosPaso4 += (float) $cargo->monto;

                    $listaCargos[] = [
                        'nombre'   => $cargo->concepto,
                        'cantidad' => 1,
                        'total'    => $cargo->monto
                    ];
                }
            } else {
                $cargosExtrasDB = DB::table('cargo_adicional')
                    ->where('id_reservacion', $idReservacion)
                    ->get();

                // Si también quieres procesar los de reservación (por si acaso), haz lo mismo aquí:
                foreach ($cargosExtrasDB as $cargo) {
                    $totalCargosPaso4 += (float) $cargo->monto;
                    $listaCargos[] = [
                        'nombre'   => $cargo->concepto,
                        'cantidad' => 1,
                        'total'    => $cargo->monto
                    ];
                }
            }

            // Matemática de Totales (Añadido $totalCargosPaso4)
            $precioRentaDia = ($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base;
            $montoRentaBase = $precioRentaDia * $dias;

            $subtotalReal = $montoRentaBase + $seguros['total'] + $totalServiciosAdic + $totalCargosExtra + $totalCargosPaso4;
            $ivaReal = $subtotalReal * 0.16;
            $totalReal = $subtotalReal + $ivaReal;

            // Sincronización DB
            DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                'subtotal' => $subtotalReal,
                'impuestos' => $ivaReal,
                'total' => $totalReal,
                'updated_at' => now()
            ]);

            $pagosRealizados = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->where('estatus', 'paid')
                ->sum('monto') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'codigo'   => $res->codigo,
                    'numero_contrato' => $res->numero_contrato ?? 'SIN GENERAR',
                    'id_contrato'     => $res->contrato_id,
                    'cliente'  => [
                        'nombre'   => $res->nombre_cliente,
                        'telefono' => $res->telefono_cliente,
                        'email'    => $res->email_cliente
                    ],
                    'vehiculo' => $res->veh_id ? [
                        'id_vehiculo' => $res->veh_id,
                        'marca'       => $res->veh_marca,
                        'modelo'      => $res->veh_modelo,
                        'categoria'   => $res->veh_categoria,
                        'transmision' => $res->veh_transmision,
                        'km'          => $res->veh_km,
                        'imagen'      => null,
                        'placa'       => $res->veh_placa,
                        'asientos'    => $res->veh_asientos,
                        'puertas'     => $res->veh_puertas
                    ] : null,
                    'entrega'  => $entregaInfo,
                    'costo_km' => $res->precio_km_dropoff ?? 0,

                    'fechas'   => [
                        'inicio'      => $res->fecha_inicio,
                        'hora_inicio' => \Carbon\Carbon::parse($res->hora_retiro)->format('h:i A'),
                        'fin'         => $res->fecha_fin,
                        'hora_fin'    => \Carbon\Carbon::parse($res->hora_entrega)->format('h:i A'),
                        'dias'        => $dias
                    ],
                    'seguros'   => $seguros,
                    'servicios' => $listaServicios,

                    'cargos'    => $listaCargos,

                    'totales'   => [
                        'tarifa_base'       => $res->tarifa_base,
                        'tarifa_modificada' => $res->tarifa_modificada,
                        'subtotal'          => $subtotalReal,
                        'iva'               => $ivaReal,
                        'total'             => $totalReal,
                        // 'servicios_total' => ($totalServiciosAdic + $totalCargosExtra + $totalCargosPaso4),
                        'servicios_total' => ($totalServiciosAdic + $totalCargosExtra),
                        'horas_cortesia'    => $res->horas_cortesia
                    ],
                    'pagos' => [
                        'realizados' => $pagosRealizados,
                        'saldo'      => max(0, $totalReal - $pagosRealizados)
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR resumenContrato: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
