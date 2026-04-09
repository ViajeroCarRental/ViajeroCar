<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContratoBaseController extends Controller
{
    protected function mapaImagenesCategoria(): array
    {
        return [
            'C'  => 'aveo.png',
            'D'  => 'virtus.png',
            'E'  => 'jetta.png',
            'F'  => 'camry.png',
            'IC' => 'renegade.png',
            'I'  => 'taos.png',
            'IB' => 'avanza.png',
            'M'  => 'Odyssey.png',
            'L'  => 'Hiace.png',
            'H'  => 'Frontier.png',
            'HI' => 'Tacoma.png',
        ];
    }

    protected function imagenContratoPorCategoria(?string $codigoCategoria): string
    {
        $archivo = $this->mapaImagenesCategoria()[$codigoCategoria ?? ''] ?? 'Logotipo.png';

        return asset("img/{$archivo}");
    }

    protected function calcularDiasRenta($fechaInicio, $fechaFin): int
    {
        return max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)));
    }

    public function resumenContrato($idReservacion)
    {
        try {
            $res = DB::table('reservaciones as r')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->leftJoin('categorias_carros as cc', 'r.id_categoria', '=', 'cc.id_categoria')
                ->select(
                    'r.*',
                    'v.id_vehiculo as vehiculo_id',
                    'v.marca as vehiculo_marca',
                    'v.modelo as vehiculo_modelo',
                    'v.nombre_publico as vehiculo_nombre_publico',
                    'v.categoria as vehiculo_categoria',
                    'v.transmision as vehiculo_transmision',
                    'v.kilometraje as vehiculo_km',
                    'v.placa as vehiculo_placa',
                    'v.asientos as vehiculo_asientos',
                    'v.puertas as vehiculo_puertas',
                    'cc.codigo as codigo_categoria'
                )
                ->where('r.id_reservacion', $idReservacion)
                ->first();
            if (!$res) return response()->json(['success' => false, 'msg' => 'Reservacion no encontrada'], 404);

            $codigoCat = $res->codigo_categoria ?? 'C';

            // --- LÓGICA DE IMAGEN FIJA POR CATEGORÍA ---
            $imgFinal = $this->imagenContratoPorCategoria($codigoCat);
            $dias = $this->calcularDiasRenta($res->fecha_inicio, $res->fecha_fin);

            $seguros = ['tipo' => null, 'lista' => [], 'total' => 0];
            $listaServicios = [];
            $sumaServicios = 0;

            $listaCargos = [];
            $sumaCargos = 0;

            // 4. SEGUROS
            $paquete = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
                ->where('rps.id_reservacion', $idReservacion)
                ->select('sp.nombre', 'rps.precio_por_dia')->first();

            if ($paquete) {
                $seguros['tipo'] = 'paquete';
                $seguros['lista'][] = ['nombre' => $paquete->nombre, 'precio' => $paquete->precio_por_dia];
                $seguros['total'] = (float)$paquete->precio_por_dia * $dias;
            } else {
                $individuales = DB::table('reservacion_seguro_individual as ri')
                    ->join('seguro_individuales as si', 'ri.id_individual', '=', 'si.id_individual')
                    ->where('ri.id_reservacion', $idReservacion)
                    ->select('si.nombre', 'ri.precio_por_dia')->get();
                foreach ($individuales as $ind) {
                    $seguros['lista'][] = ['nombre' => $ind->nombre, 'precio' => $ind->precio_por_dia];
                    $seguros['total'] += ((float)$ind->precio_por_dia * $dias);
                }
            }

            // 5. DELIVERY
            if ($res->delivery_activo && (float)$res->delivery_total > 0) {
                $montoDev = (float)$res->delivery_total;
                $listaServicios[] = ['nombre' => 'Delivery', 'cantidad' => 1, 'total' => $montoDev];
                $sumaServicios += $montoDev;
            }

            // 6. SERVICIOS DEL PASO 2
            $serviciosDB = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->where('rs.id_reservacion', $idReservacion)
                ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario', 's.tipo_cobro')->get();

            foreach ($serviciosDB as $srv) {
                $sub = ($srv->tipo_cobro === 'por_dia')
                    ? ((float)$srv->cantidad * (float)$srv->precio_unitario * $dias)
                    : ((float)$srv->cantidad * (float)$srv->precio_unitario);
                $listaServicios[] = ['nombre' => $srv->nombre, 'cantidad' => $srv->cantidad, 'total' => $sub];
                $sumaServicios += $sub;
            }

            // 7. CARGOS ADICIONALES (Dropoff y Gasolina)
            $idContrato = DB::table('contratos')
                ->where('id_reservacion', $idReservacion)
                ->orderBy('id_contrato', 'desc')
                ->value('id_contrato');

            if ($idContrato) {
                $cargosExtras = DB::table('cargo_adicional')
                    ->where('id_contrato', $idContrato)
                    ->get();

                foreach ($cargosExtras as $cargo) {
                    $monto = (float)($cargo->monto ?? 0);

                    if ($monto <= 0) continue;

                    $nombre = $cargo->concepto;
                    if (empty($nombre)) {
                        if ($cargo->id_concepto == 5) $nombre = 'Gasolina Prepago';
                        elseif ($cargo->id_concepto == 6) $nombre = 'Dropoff';
                        else $nombre = 'Cargo Adicional';
                    }

                    // 🔥 CORRECCIÓN: Se agregan a listaCargos y sumaCargos, no a servicios
                    $listaCargos[] = ['nombre' => $nombre, 'cantidad' => 1, 'total' => $monto];
                    $sumaCargos += $monto;
                }
            }

            // 8. TOTALES FINALES
            $precioRentaDia = (float)(($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base);
            $montoRentaBase = $precioRentaDia * $dias;

            $subtotalReal = $montoRentaBase + $seguros['total'] + $sumaServicios + $sumaCargos;
            $ivaReal = $subtotalReal * 0.16;
            $totalReal = $subtotalReal + $ivaReal;

            // 🚀 OPTIMIZACIÓN: Solo actualiza si los valores cambiaron (ahorra milisegundos de DB)
            

            $pagosRealizados = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->where('estatus', 'paid')
                ->where(function ($query) {
                    $query->whereNull('tipo_pago')
                        ->orWhereRaw('UPPER(TRIM(tipo_pago)) <> ?', ['GARANTIA']);
                })
                ->sum('monto') ?? 0;
            $vehiculoData = $res->vehiculo_id ? [
                'id_vehiculo'      => $res->vehiculo_id,
                'marca'            => $res->vehiculo_marca,
                'modelo'           => $res->vehiculo_modelo,
                'nombre_publico'   => $res->vehiculo_nombre_publico,
                'categoria'        => $res->vehiculo_categoria,
                'codigo_categoria' => $codigoCat,
                'transmision'      => $res->vehiculo_transmision,
                'km'               => $res->vehiculo_km,
                'placa'            => $res->vehiculo_placa,
                'asientos'         => $res->vehiculo_asientos,
                'puertas'          => $res->vehiculo_puertas,
                'imagen_render'    => $imgFinal
            ] : [
                'imagen_render'    => $imgFinal,
                'categoria'        => $codigoCat,
                'codigo_categoria' => $codigoCat,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'codigo' => $res->codigo,
                    'cliente' => ['nombre' => $res->nombre_cliente, 'telefono' => $res->telefono_cliente, 'email' => $res->email_cliente],
                    'vehiculo' => $vehiculoData,
                    'fechas' => [
                        'inicio' => $res->fecha_inicio,
                        'hora_inicio' => \Carbon\Carbon::parse($res->hora_retiro)->format('h:i A'),
                        'fin' => $res->fecha_fin,
                        'hora_fin' => \Carbon\Carbon::parse($res->hora_entrega)->format('h:i A'),
                        'dias' => $dias
                    ],
                    'seguros' => $seguros,
                    'servicios' => $listaServicios,
                    'cargos' => $listaCargos,
                    'totales' => [
                        'tarifa_base' => $precioRentaDia,
                        'tarifa_modificada' => (float)($res->tarifa_modificada ?? 0),
                        'subtotal' => $subtotalReal,
                        'iva' => $ivaReal,
                        'total' => $totalReal,
                        'servicios_total' => $sumaServicios,
                        'cargos_total' => $sumaCargos,
                        'horas_cortesia' => $res->horas_cortesia
                    ],
                    'pagos' => ['realizados' => $pagosRealizados, 'saldo' => max(0, $totalReal - $pagosRealizados)]
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR resumenContrato: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Asigna un vehículo específico a una reservación y actualiza su historial de estatus.
     */
    public function asignarVehiculo(Request $request)
    {
        try {
            // validacion: Asegura que el ID de reservación y el del vehículo existan en las tablas
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_vehiculo'    => 'required|integer|exists:vehiculos,id_vehiculo',
            ]);

            $res = DB::table('reservaciones')->where('id_reservacion', $data['id_reservacion'])->first();
            if (!$res) {
                return response()->json(['success' => false, 'error' => 'Reservacion no encontrada'], 404);
            }

            $inicioReq = $res->fecha_inicio . ' ' . $res->hora_retiro;
            $finReq = $res->fecha_fin . ' ' . $res->hora_entrega;

            $vehiculoBloqueado = DB::table('reservaciones as r')
                ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                ->where('r.id_vehiculo', $data['id_vehiculo'])
                ->where('r.id_reservacion', '!=', $data['id_reservacion'])
                ->where(function ($q) use ($inicioReq, $finReq) {
                    $q->where(function ($sub) use ($inicioReq, $finReq) {
                        $sub->whereRaw("CONCAT(r.fecha_inicio, ' ', r.hora_retiro) < ?", [$finReq])
                            ->whereRaw("CONCAT(r.fecha_fin, ' ', r.hora_entrega) > ?", [$inicioReq])
                            ->where('r.estado', 'confirmada');
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('c.id_contrato')
                            ->whereNotIn('c.estado', ['cerrado', 'cancelado']);
                    });
                })
                ->select('r.codigo')
                ->first();

            if ($vehiculoBloqueado) {
                return response()->json([
                    'success' => false,
                    'error' => 'Este vehiculo ya esta asignado a otra reservacion o contrato activo.'
                ], 422);
            }

            DB::transaction(function () use ($data, $res) {

                // A. ACTUALIZAR RESERVA: 
                // Vinculamos el ID del vehículo elegido a la fila de la reservación.
                DB::table('reservaciones')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->update([
                        'id_vehiculo' => $data['id_vehiculo'],
                        'updated_at'  => now()
                    ]);

                // B. HISTORIAL: Insertamos el registro en la tabla de estatus historial.
                // Nota: id_estatus => 2 representa que el vehículo pasa a estar "Ocupado" o "Rentado".
                DB::table('vehiculo_estatus_historial')->insert([
                    'id_vehiculo' => $data['id_vehiculo'],
                    'id_estatus'  => 2,
                    'motivo'      => 'Asignado a reservación ' . $res->codigo,
                    'cambiado_en' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return response()->json(['success' => true, 'msg' => 'Vehículo asignado y estatus actualizado.']);
        } catch (\Exception $e) {
            Log::error("Error asignarVehiculo: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => "No se pudo asignar el coche"], 500);
        }
    }

    /**
     * Obtener vehículos disponibles por categoría
     * Usado por el modal del paso 1 y paso 4 del contrato
     */
    public function vehiculosPorCategoria($idCategoria, $idReservacion)
    {
        try {
            $resActual = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
            if (!$resActual) return response()->json(['success' => false, 'error' => 'Reserva no encontrada'], 404);

            $inicioReq = $resActual->fecha_inicio . ' ' . $resActual->hora_retiro;
            $finReq    = $resActual->fecha_fin . ' ' . $resActual->hora_entrega;
            $idVehiculoActual = $resActual->id_vehiculo ?? 0;

            $vehiculos = DB::table('vehiculos as v')
                ->leftJoin('vehiculo_imagenes as img', function ($j) {
                    $j->on('img.id_vehiculo', '=', 'v.id_vehiculo')->where('img.orden', 0);
                })
                ->leftJoin('mantenimientos as m', 'm.id_vehiculo', '=', 'v.id_vehiculo')
                ->where('v.id_categoria', $idCategoria)
                ->select('v.*', 'img.url as foto_url', 'm.proximo_servicio')

                // SUB-CONSULTA DE BLOQUEO
                ->selectSub(function ($query) use ($idReservacion, $inicioReq, $finReq) {
                    $query->from('reservaciones as r')
                        ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                        ->select('r.codigo')
                        ->whereColumn('r.id_vehiculo', 'v.id_vehiculo')
                        ->where('r.id_reservacion', '!=', $idReservacion)
                        ->where(function ($q) use ($inicioReq, $finReq) {

                            /**
                             * REGLA 1 y 3: Solo bloquea si está CONFIRMADA
                             * Si la reserva está en 'hold' o 'pendiente_pago', el coche sigue LIBRE.
                             * El pago ay esta confirmado solomanete
                             */
                            $q->where(function ($sub) use ($inicioReq, $finReq) {
                                $sub->whereRaw("CONCAT(r.fecha_inicio, ' ', r.hora_retiro) < ?", [$finReq])
                                    ->whereRaw("CONCAT(r.fecha_fin, ' ', r.hora_entrega) > ?", [$inicioReq])
                                    ->where('r.estado', 'confirmada'); // <--- Cambio clave aquí
                            })

                                /**
                                 * REGLA 2: Estado del Contrato
                                 * Si ya hay un contrato físico, bloqueamos a menos que esté cerrado/cancelado.
                                 */
                                ->orWhere(function ($sub) {
                                    $sub->whereNotNull('c.id_contrato')
                                        ->whereNotIn('c.estado', ['cerrado', 'cancelado']);
                                });
                        })
                        ->limit(1);
                }, 'bloqueado_por_codigo')

                ->addSelect(DB::raw("(v.id_vehiculo = $idVehiculoActual) as es_el_actual"))
                ->get();

            // Procesamiento de datos para la vista
            $vehiculos->transform(function ($v) {
                $v->km_restantes = ($v->proximo_servicio && $v->kilometraje) ? ($v->proximo_servicio - $v->kilometraje) : null;

                if ($v->km_restantes === null) $v->color_mantenimiento = "gris";
                elseif ($v->km_restantes > 1200) $v->color_mantenimiento = "verde";
                elseif ($v->km_restantes > 600) $v->color_mantenimiento = "amarillo";
                else $v->color_mantenimiento = "rojo";

                return $v;
            });

            return response()->json(['success' => true, 'data' => $vehiculos]);
        } catch (\Exception $e) {
            Log::error("Error en vehiculosPorCategoria: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function vehiculoRandom($idCategoria)
    {
        try {
            // Buscar vehículos disponibles de esa categoría
            $vehiculos = DB::table('vehiculos')
                ->leftJoin('vehiculo_imagenes', 'vehiculos.id_vehiculo', '=', 'vehiculo_imagenes.id_vehiculo')
                ->where('vehiculos.id_categoria', $idCategoria)
                ->select(
                    'vehiculos.id_vehiculo',
                    'vehiculos.nombre_publico',
                    'vehiculos.transmision',
                    'vehiculos.asientos',
                    'vehiculos.puertas',
                    'vehiculos.color',
                    'vehiculo_imagenes.url AS foto_url'
                )
                ->inRandomOrder()
                ->first();

            if (!$vehiculos) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No hay vehículos disponibles para esta categoría'
                ]);
            }

            return response()->json([
                'success'  => true,
                'vehiculo' => $vehiculos
            ]);
        } catch (\Throwable $e) {
            Log::error("Error vehiculoRandom: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'Error interno'
            ], 500);
        }
    }

    public function guardarCargoVariable(Request $request)
    {
        try {
            $idContrato = $request->id_contrato;
            $idConcepto = $request->id_concepto;

            if (!$idConcepto || !$idContrato) {
                return response()->json(['success' => false, 'msg' => 'Falta ID de contrato o concepto']);
            }

            $montoVariable = (float) ($request->monto_variable ?? 0);
            $kilometros    = $request->km ?? null;
            $destino       = $request->destino ?? null;
            $litros        = $request->litros ?? null;
            $precioLitro   = $request->precio_litro ?? null;

            $json = [
                'km'           => $kilometros,
                'destino'      => $destino,
                'litros'       => $litros,
                'precio_litro' => $precioLitro,
                'monto'        => $montoVariable,
            ];

            $query = DB::table('cargo_adicional')
                ->where('id_concepto', $idConcepto)
                ->where('id_contrato', $idContrato);

            $existe = $query->first();

            $esApagado = ($montoVariable <= 0 && $idConcepto == 5) || ($montoVariable <= 0 && $idConcepto == 6);

            if ($esApagado) {
                // Si el monto es 0, borramos el registro de la tabla para que no salga en el resumen
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            if ($existe) {
                $query->update([
                    'monto'      => $montoVariable,
                    'detalle'    => json_encode($json),
                    'updated_at' => now()
                ]);
                $action = 'updated';
            } else {
                $nombreConcepto = DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->value('nombre');
                if (!$nombreConcepto) {
                    $nombreConcepto = match ((int)$idConcepto) {
                        5 => 'Gasolina Prepago',
                        6 => 'Servicio de Dropoff',
                        default => 'Cargo Adicional'
                    };
                }

                DB::table('cargo_adicional')->insert([
                    'id_contrato' => $idContrato,
                    'id_concepto' => $idConcepto,
                    'concepto'    => $nombreConcepto,
                    'monto'       => $montoVariable,
                    'detalle'     => json_encode($json),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $action = 'inserted';
            }

            return response()->json(['success' => true, 'action' => $action]);
        } catch (\Exception $e) {
            Log::error("ERROR guardarCargoVariable: " . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Error SQL: ' . $e->getMessage()], 500);
        }
    }

    protected function calcularTotalProtecciones($idReservacion)
    {
        $res = DB::table('reservaciones')
            ->select('fecha_inicio', 'fecha_fin')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) return 0;

        $inicio = \Carbon\Carbon::parse($res->fecha_inicio);
        $fin = \Carbon\Carbon::parse($res->fecha_fin);

        $dias = max(1, $inicio->diffInDays($fin));

        $paquete = DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if ($paquete) {
            return floatval($paquete->precio_por_dia) * $dias;
        }

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
