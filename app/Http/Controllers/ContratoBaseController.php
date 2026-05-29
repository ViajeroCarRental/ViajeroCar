<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContratoBaseController extends Controller
{
    // ---------------------------------------------------------------------
    // Constantes y configuración
    // ---------------------------------------------------------------------

    /** @var float IVA aplicable a los totales */
    private const IVA = 0.16;

    /** @var array Nombres de servicios que se manejan como cargos adicionales y no deben duplicarse */
    private const SERVICIOS_EXCLUIDOS = [
        'Gasolina Prepago',
        'Drop Off',
        'Dropoff',
        'Delivery',
        'Servicio de Delivery',
        'Prepaid fuel',
    ];

    /** @var array Mapa de códigos de categoría a imagen de contrato */
    private const MAPA_IMAGENES_CATEGORIA = [
        'C'  => 'aveo.webp',
        'D'  => 'virtus.webp',
        'E'  => 'jetta.webp',
        'F'  => 'camry.webp',
        'IC' => 'renegade.webp',
        'I'  => 'taos.webp',
        'IB' => 'avanza.webp',
        'M'  => 'Odyssey.webp',
        'L'  => 'Hiace.webp',
        'H'  => 'Frontier.webp',
        'HI' => 'Tacoma.webp',
    ];

    // ---------------------------------------------------------------------
    // Helpers básicos (reutilizables)
    // ---------------------------------------------------------------------

    /** Obtiene la imagen de contrato según la categoría de la reserva */
    protected function imagenContratoPorCategoria(?string $codigoCategoria): string
    {
        $archivo = self::MAPA_IMAGENES_CATEGORIA[$codigoCategoria ?? ''] ?? 'Logotipo.png';
        return asset("img/{$archivo}");
    }

    /** Calcula los días de renta entre dos fechas, mínimo 1 día */
    protected function calcularDiasRenta($fechaInicio, $fechaFin): int
    {
        return max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)));
    }

    /** Obtiene el IVA configurado (puede venir de BD en el futuro) */
    protected function getIva(): float
    {
        return self::IVA;
    }

    // ---------------------------------------------------------------------
    // Lógica central: resumen del contrato
    // ---------------------------------------------------------------------

    public function resumenContrato($idReservacion)
    {
        try {
            $res = $this->obtenerReservacionBase($idReservacion);
            if (!$res) {
                return response()->json(['success' => false, 'msg' => 'Reservación no encontrada'], 404);
            }

            $dias = $this->calcularDiasRenta($res->fecha_inicio, $res->fecha_fin);
            $codigoCat = $res->codigo_categoria ?? 'C';

            // Componentes del resumen
            $vehiculoData = $this->buildVehiculoData($res, $codigoCat);
            $seguros      = $this->buildSeguros($idReservacion, $dias);
            $servicios    = $this->buildServicios($idReservacion, $dias);
            $cargos       = $this->buildCargosAdicionales($idReservacion);
            $totales      = $this->buildTotales($res, $dias, $seguros, $servicios, $cargos);
            $pagos        = $this->buildPagos($idReservacion, $totales['total']);

            return response()->json([
                'success' => true,
                'data' => [
                    'codigo'   => $res->codigo,
                    'cliente'  => [
                        'nombre'   => $res->nombre_cliente,
                        'telefono' => $res->telefono_cliente,
                        'email'    => $res->email_cliente,
                    ],
                    'vehiculo'  => $vehiculoData,
                    'fechas'    => [
                        'inicio'      => $res->fecha_inicio,
                        'hora_inicio' => Carbon::parse($res->hora_retiro)->format('h:i A'),
                        'fin'         => $res->fecha_fin,
                        'hora_fin'    => Carbon::parse($res->hora_entrega)->format('h:i A'),
                        'dias'        => $dias,
                    ],
                    'seguros'   => $seguros,
                    'servicios' => $servicios['lista'],
                    'cargos'    => $cargos['lista'],
                    'totales'   => $totales,
                    'pagos'     => $pagos,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR resumenContrato: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------------
    // Subconsultas del resumen (privadas)
    // ---------------------------------------------------------------------

    private function obtenerReservacionBase(int $idReservacion)
    {
        return DB::table('reservaciones as r')
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
                'cc.codigo as codigo_categoria',
                'cc.nombre as categoria_nombre_formal'
            )
            ->where('r.id_reservacion', $idReservacion)
            ->first();
    }

    private function buildVehiculoData($res, string $codigoCat): array
    {
        $imgFinal = $this->imagenContratoPorCategoria($codigoCat);

        if ($res->vehiculo_id) {
            return [
                'id_vehiculo'      => $res->vehiculo_id,
                'id_categoria'     => $res->id_categoria, // ID real de la categoría
                'marca'            => $res->vehiculo_marca,
                'modelo'           => $res->vehiculo_modelo,
                'nombre_publico'   => $res->vehiculo_nombre_publico,
                'categoria'        => $res->categoria_nombre_formal, // Usar nombre de la tabla categorias_carros
                'codigo_categoria' => $codigoCat,
                'transmision'      => $res->vehiculo_transmision,
                'km'               => $res->vehiculo_km,
                'placa'            => $res->vehiculo_placa,
                'asientos'         => $res->vehiculo_asientos,
                'puertas'          => $res->vehiculo_puertas,
                'imagen_render'    => $imgFinal,
            ];
        }

        return [
            'imagen_render'    => $imgFinal,
            'id_categoria'     => $res->id_categoria,
            'categoria'        => $res->categoria_nombre_formal,
            'codigo_categoria' => $codigoCat,
        ];
    }

    private function buildSeguros(int $idReservacion, int $dias): array
    {
        $seguros = ['tipo' => null, 'lista' => [], 'total' => 0];

        $paquete = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->where('rps.id_reservacion', $idReservacion)
            ->select('sp.nombre', 'rps.precio_por_dia')
            ->first();

        if ($paquete) {
            $seguros['tipo'] = 'paquete';
            $seguros['lista'][] = ['nombre' => $paquete->nombre, 'precio' => $paquete->precio_por_dia];
            $seguros['total'] = (float) $paquete->precio_por_dia * $dias;
        } else {
            $individuales = DB::table('reservacion_seguro_individual as ri')
                ->join('seguro_individuales as si', 'ri.id_individual', '=', 'si.id_individual')
                ->where('ri.id_reservacion', $idReservacion)
                ->select('si.nombre', 'ri.precio_por_dia')
                ->get();

            foreach ($individuales as $ind) {
                $seguros['lista'][] = ['nombre' => $ind->nombre, 'precio' => $ind->precio_por_dia];
                $seguros['total'] += (float) $ind->precio_por_dia * $dias;
            }
        }

        return $seguros;
    }

    private function buildServicios(int $idReservacion, int $dias): array
    {
        $lista   = [];
        $suma    = 0;

        // Delivery (si existe como campo en la reservación)
        $res = DB::table('reservaciones')
            ->select('delivery_activo', 'delivery_total')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if ($res && $res->delivery_activo && (float) $res->delivery_total > 0) {
            $monto = (float) $res->delivery_total;
            $lista[] = ['nombre' => 'Delivery', 'cantidad' => 1, 'total' => $monto];
            $suma += $monto;
        }

        // Servicios extra excluyendo los que son cargos adicionales
        $serviciosDB = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->where('rs.id_reservacion', $idReservacion)
            ->whereNotIn('s.nombre', self::SERVICIOS_EXCLUIDOS)
            ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario', 's.tipo_cobro')
            ->get();

        foreach ($serviciosDB as $srv) {
            $sub = ($srv->tipo_cobro === 'por_dia')
                ? (float) $srv->cantidad * (float) $srv->precio_unitario * $dias
                : (float) $srv->cantidad * (float) $srv->precio_unitario;

            $lista[] = ['nombre' => $srv->nombre, 'cantidad' => $srv->cantidad, 'total' => $sub];
            $suma += $sub;
        }

        return ['lista' => $lista, 'total' => $suma];
    }

    private function buildCargosAdicionales(int $idReservacion): array
    {
        $lista = [];
        $suma  = 0;

        $idContrato = DB::table('contratos')
            ->where('id_reservacion', $idReservacion)
            ->orderBy('id_contrato', 'desc')
            ->value('id_contrato');

        if (!$idContrato) {
            return ['lista' => $lista, 'total' => $suma];
        }

        $cargosExtras = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->get();

        foreach ($cargosExtras as $cargo) {
            $monto = (float) ($cargo->monto ?? 0);
            if ($monto <= 0) {
                continue;
            }

            $nombre = $cargo->concepto;
            if (empty($nombre)) {
                $nombre = match ((int) $cargo->id_concepto) {
                    5 => 'Gasolina Prepago',
                    6 => 'Dropoff',
                    default => 'Cargo Adicional',
                };
            }

            $lista[] = ['nombre' => $nombre, 'cantidad' => 1, 'total' => $monto];
            $suma += $monto;
        }

        return ['lista' => $lista, 'total' => $suma];
    }

    private function buildTotales($res, int $dias, array $seguros, array $servicios, array $cargos): array
    {
        $precioRentaDia = (float) (($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base);
        $montoRentaBase = $precioRentaDia * $dias;

        $subtotalReal = $montoRentaBase + $seguros['total'] + $servicios['total'] + $cargos['total'];
        $ivaReal = $subtotalReal * $this->getIva();
        $totalReal = $subtotalReal + $ivaReal;

        return [
            'tarifa_base'       => $precioRentaDia,
            'tarifa_modificada' => (float) ($res->tarifa_modificada ?? 0),
            'subtotal'          => $subtotalReal,
            'iva'               => $ivaReal,
            'total'             => $totalReal,
            'servicios_total'   => $servicios['total'],
            'cargos_total'      => $cargos['total'],
            'horas_cortesia'    => $res->horas_cortesia,
        ];
    }

    private function buildPagos(int $idReservacion, float $totalReal): array
    {
        $pagosRealizados = DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->where(function ($query) {
                $query->whereNull('tipo_pago')
                      ->orWhereRaw('UPPER(TRIM(tipo_pago)) <> ?', ['GARANTIA']);
            })
            ->sum('monto') ?? 0;

        return [
            'realizados' => $pagosRealizados,
            'saldo'      => max(0, $totalReal - $pagosRealizados),
        ];
    }

    // ---------------------------------------------------------------------
    // Asignación de vehículo
    // ---------------------------------------------------------------------

    public function asignarVehiculo(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_vehiculo'    => 'required|integer|exists:vehiculos,id_vehiculo',
            ]);

            $res = DB::table('reservaciones')->where('id_reservacion', $data['id_reservacion'])->first();
            if (!$res) {
                return response()->json(['success' => false, 'error' => 'Reservación no encontrada'], 404);
            }

            // Obtener información del vehículo y su categoría
            $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $data['id_vehiculo'])->first();
            if (!$vehiculo) {
                return response()->json(['success' => false, 'error' => 'Vehículo no encontrado'], 404);
            }

            $categoria = DB::table('categorias_carros')->where('id_categoria', $vehiculo->id_categoria)->first();
            if (!$categoria) {
                return response()->json(['success' => false, 'error' => 'Categoría del vehículo no encontrada'], 404);
            }

            $inicioReq = $res->fecha_inicio . ' ' . $res->hora_retiro;
            $finReq    = $res->fecha_fin . ' ' . $res->hora_entrega;

            if ($this->vehiculoEstaBloqueado($data['id_vehiculo'], $inicioReq, $finReq, $data['id_reservacion'])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Este vehículo ya está asignado a otra reservación o contrato activo.'
                ], 422);
            }

            DB::transaction(function () use ($data, $res, $vehiculo, $categoria) {
                // Actualizamos vehículo, categoría y tarifa base de la reservación
                DB::table('reservaciones')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->update([
                        'id_vehiculo'  => $data['id_vehiculo'],
                        'id_categoria' => $vehiculo->id_categoria,
                        'tarifa_base'  => $categoria->precio_dia,
                        'updated_at'   => now(),
                    ]);

                DB::table('vehiculo_estatus_historial')->insert([
                    'id_vehiculo' => $data['id_vehiculo'],
                    'id_estatus'  => 2,
                    'motivo'      => 'Asignado a reservación ' . $res->codigo,
                    'cambiado_en' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            return response()->json(['success' => true, 'msg' => 'Vehículo asignado y categoría actualizada.']);
        } catch (\Exception $e) {
            Log::error("Error asignarVehiculo: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => "No se pudo asignar el coche"], 500);
        }
    }

    // ---------------------------------------------------------------------
    // Vehículos por categoría (modal)
    // ---------------------------------------------------------------------

    public function vehiculosPorCategoria($idCategoria, $idReservacion)
    {
        try {
            $resActual = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
            if (!$resActual) {
                return response()->json(['success' => false, 'error' => 'Reserva no encontrada'], 404);
            }

            $inicioReq = $resActual->fecha_inicio . ' ' . $resActual->hora_retiro;
            $finReq    = $resActual->fecha_fin . ' ' . $resActual->hora_entrega;
            $idVehiculoActual = $resActual->id_vehiculo ?? 0;

            $query = DB::table('vehiculos as v')
                ->leftJoin('categorias_carros as cc', 'v.id_categoria', '=', 'cc.id_categoria')
                ->leftJoin('vehiculo_imagenes as img', function ($j) {
                    $j->on('img.id_vehiculo', '=', 'v.id_vehiculo')->where('img.orden', 0);
                })
                ->leftJoin('mantenimientos as m', 'm.id_vehiculo', '=', 'v.id_vehiculo')
                ->select(
                    'v.*', 
                    'cc.nombre as categoria_nombre', 
                    'cc.codigo as categoria_codigo',
                    'img.url as foto_url', 
                    'm.proximo_servicio'
                )
                ->selectSub(
                    $this->subqueryBloqueo($inicioReq, $finReq, $idReservacion),
                    'bloqueado_por_codigo'
                )
                ->addSelect(DB::raw("(v.id_vehiculo = {$idVehiculoActual}) as es_el_actual"));

            // Si es 'todos', no filtramos por categoría
            if ($idCategoria !== 'todos') {
                $query->where('v.id_categoria', $idCategoria);
            }

            $vehiculos = $query->orderBy('cc.orden')->orderBy('v.placa')->get();

            $vehiculos->transform(function ($v) {
                $v->km_restantes = ($v->proximo_servicio && $v->kilometraje)
                    ? ($v->proximo_servicio - $v->kilometraje)
                    : null;

                if ($v->km_restantes === null) {
                    $v->color_mantenimiento = "gris";
                } elseif ($v->km_restantes > 1200) {
                    $v->color_mantenimiento = "verde";
                } elseif ($v->km_restantes > 600) {
                    $v->color_mantenimiento = "amarillo";
                } else {
                    $v->color_mantenimiento = "rojo";
                }

                return $v;
            });

            return response()->json(['success' => true, 'data' => $vehiculos]);
        } catch (\Exception $e) {
            Log::error("Error en vehiculosPorCategoria: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------------
    // Vehículo aleatorio (solo catálogo)
    // ---------------------------------------------------------------------

    public function vehiculoRandom($idCategoria)
    {
        try {
            $vehiculo = DB::table('vehiculos')
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

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'error'   => 'No hay vehículos disponibles para esta categoría'
                ]);
            }

            return response()->json([
                'success'  => true,
                'vehiculo' => $vehiculo
            ]);
        } catch (\Throwable $e) {
            Log::error("Error vehiculoRandom: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Error interno'
            ], 500);
        }
    }

    // ---------------------------------------------------------------------
    // Cargo variable
    // ---------------------------------------------------------------------

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

            $detalle = [
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

            // Si el monto es 0 y es un concepto apagable, se elimina el registro
            $esApagado = ($montoVariable <= 0 && in_array($idConcepto, [5, 6]));
            if ($esApagado) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            if ($existe) {
                $query->update([
                    'monto'      => $montoVariable,
                    'detalle'    => json_encode($detalle),
                    'updated_at' => now(),
                ]);
                $action = 'updated';
            } else {
                $nombreConcepto = DB::table('cargo_concepto')
                    ->where('id_concepto', $idConcepto)
                    ->value('nombre');

                if (!$nombreConcepto) {
                    $nombreConcepto = match ((int) $idConcepto) {
                        5 => 'Gasolina Prepago',
                        6 => 'Servicio de Dropoff',
                        default => 'Cargo Adicional',
                    };
                }

                DB::table('cargo_adicional')->insert([
                    'id_contrato' => $idContrato,
                    'id_concepto' => $idConcepto,
                    'concepto'    => $nombreConcepto,
                    'monto'       => $montoVariable,
                    'detalle'     => json_encode($detalle),
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

    // ---------------------------------------------------------------------
    // Métodos auxiliares de disponibilidad y reutilizables
    // ---------------------------------------------------------------------

    /**
     * Subconsulta que devuelve el código de la reservación que bloquea al vehículo,
     * o NULL si está libre.
     */
    private function subqueryBloqueo(string $inicio, string $fin, int $idReservacionExcluir)
    {
        return function ($query) use ($inicio, $fin, $idReservacionExcluir) {
            $query->from('reservaciones as r')
                ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                ->select('r.codigo')
                ->whereColumn('r.id_vehiculo', 'v.id_vehiculo')
                ->where('r.id_reservacion', '!=', $idReservacionExcluir)
                ->where(function ($q) use ($inicio, $fin) {
                    $q->where(function ($sub) use ($inicio, $fin) {
                        $sub->whereRaw("CONCAT(r.fecha_inicio, ' ', r.hora_retiro) < ?", [$fin])
                            ->whereRaw("CONCAT(r.fecha_fin, ' ', r.hora_entrega) > ?", [$inicio])
                            ->where('r.estado', 'confirmada');
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('c.id_contrato')
                            ->whereNotIn('c.estado', ['cerrado', 'cancelado']);
                    });
                })
                ->limit(1);
        };
    }

    /**
     * Verifica si un vehículo tiene conflictos de calendario con otra reservación o contrato.
     */
    private function vehiculoEstaBloqueado(int $idVehiculo, string $inicio, string $fin, int $excluirReservaId): bool
    {
        return DB::table('reservaciones as r')
            ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
            ->where('r.id_vehiculo', $idVehiculo)
            ->where('r.id_reservacion', '!=', $excluirReservaId)
            ->where(function ($q) use ($inicio, $fin) {
                $q->where(function ($sub) use ($inicio, $fin) {
                    $sub->whereRaw("CONCAT(r.fecha_inicio, ' ', r.hora_retiro) < ?", [$fin])
                        ->whereRaw("CONCAT(r.fecha_fin, ' ', r.hora_entrega) > ?", [$inicio])
                        ->where('r.estado', 'confirmada');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('c.id_contrato')
                        ->whereNotIn('c.estado', ['cerrado', 'cancelado']);
                });
            })
            ->exists();
    }

    // ---------------------------------------------------------------------
    // Protecciones (mantenidos por compatibilidad)
    // ---------------------------------------------------------------------

    protected function calcularTotalProtecciones($idReservacion)
    {
        $res = DB::table('reservaciones')
            ->select('fecha_inicio', 'fecha_fin')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) return 0;

        $dias = $this->calcularDiasRenta($res->fecha_inicio, $res->fecha_fin);

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