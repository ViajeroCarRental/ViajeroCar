<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class ContratoController extends ContratoBaseController
{
    /**
     * 📄 Mostrar un contrato específico a partir del ID de reservación.
     */
    public function mostrarContrato($id)
    {
        try {
            // 👤 Asesor logueado (desde sesión)
            $asesorId = session('id_usuario'); // puede ser null si no hay sesión

            // =========================================================
            // 0) Detectar si $id es id_contrato o id_reservacion
            // =========================================================
            // Primero intentamos encontrar un contrato con ese ID
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if ($contrato) {
                // 👉 Venimos desde "Contratos Abiertos" (ID = id_contrato)
                $idReservacion = $contrato->id_reservacion;
            } else {
                // 👉 Venimos desde algún flujo que sigue usando id_reservacion
                $idReservacion = $id;
            }

            // =========================================================
            // 1) Obtener la reservación base (usando SIEMPRE id_reservacion real)
            // =========================================================
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
                ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',
                    'r.nombre_cliente',
                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.fecha_inicio',
                    'r.fecha_fin',
                    'r.hora_retiro',
                    'r.hora_entrega',
                    'r.subtotal',      // Actualizar total
                    'r.impuestos',     // Actualizar total
                    'r.total',
                    'sr.nombre as sucursal_retiro_nombre',
                    'se.nombre as sucursal_entrega_nombre',
                    'r.id_vehiculo',
                    'r.id_categoria'
                )
                ->where('r.id_reservacion', $idReservacion)
                ->first();

            if (!$reservacion) {
                return redirect()->back()->with('error', 'Reservación no encontrada.');
            }
            //=========================================================
            if (!$contrato) {

                $timezone = 'America/Mexico_City';

                /*
                |--------------------------------------------------------------------------
                | 1) Fechas
                |--------------------------------------------------------------------------
                */
                $fechaInicioOriginal = \Carbon\Carbon::parse($reservacion->fecha_inicio)
                    ->setTimezone($timezone)
                    ->startOfDay();

                $fechaInicioNueva = \Carbon\Carbon::now($timezone)->startOfDay();

                $fechaFin = \Carbon\Carbon::parse($reservacion->fecha_fin)
                    ->setTimezone($timezone)
                    ->startOfDay();

                /*
                |--------------------------------------------------------------------------
                | 2) Días
                |--------------------------------------------------------------------------
                */
                $diasOriginales = max(1, $fechaInicioOriginal->diffInDays($fechaFin));
                $diasNuevos     = max(1, $fechaInicioNueva->diffInDays($fechaFin));

                $diferenciaDias = $diasNuevos - $diasOriginales;

                /*
                |--------------------------------------------------------------------------
                | 3) Precio por día (CATEGORÍA)
                |--------------------------------------------------------------------------
                */
                $precioDia = DB::table('categorias_carros')
                    ->where('id_categoria', $reservacion->id_categoria)
                    ->value('precio_dia') ?? 0;

                /*
                |--------------------------------------------------------------------------
                | 4) Ajustes SOLO si hay diferencia
                |--------------------------------------------------------------------------
                */
                $ajusteSubtotal = $diferenciaDias * $precioDia;
                $ajusteIVA      = $ajusteSubtotal * 0.16;

                $nuevoSubtotal  = $reservacion->subtotal + $ajusteSubtotal;
                $nuevoImpuestos = $reservacion->impuestos + $ajusteIVA;
                $nuevoTotal     = $nuevoSubtotal + $nuevoImpuestos;

                $subtotal = $nuevoSubtotal;
                $impuestos = $nuevoImpuestos;
                $total = $nuevoTotal;
                /*
                |--------------------------------------------------------------------------
                | 5) Guardar cambios
                |--------------------------------------------------------------------------
                */
                DB::table('reservaciones')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->update([
                        'fecha_inicio' => $fechaInicioNueva->toDateString(),
                        'subtotal'     => $subtotal,
                        'impuestos'    => $impuestos,
                        'total'        => $total,
                        'updated_at'   => now(),
                    ]);

                /*
                |--------------------------------------------------------------------------
                | 6) Mantener objeto sincronizado
                |--------------------------------------------------------------------------
                */
                $reservacion->fecha_inicio = $fechaInicioNueva->toDateString();
                $reservacion->subtotal     = $subtotal;
                $reservacion->impuestos    = $impuestos;
                $reservacion->total        = $total;
            }

            // =========================================================
            // 2) Buscar o crear contrato para esta reservación
            //    (si no lo encontramos por id_contrato, lo buscamos por id_reservacion)
            // =========================================================
            if (!$contrato) {
                $contrato = DB::table('contratos')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->first();
            }

            if (!$contrato) {
                // 🆕 Crear contrato
                $numeroContrato = 'CTR-' . strtoupper(substr($reservacion->codigo, 0, 4)) . '-' . now()->format('ymdHis');

                $idContrato = DB::table('contratos')->insertGetId([
                    'id_reservacion'  => $reservacion->id_reservacion,
                    'id_asesor'       => $asesorId,
                    'numero_contrato' => $numeroContrato,
                    'estado'          => 'abierto',
                    'abierto_en'      => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $serviciosPactados = DB::table('reservacion_servicio')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->whereIn('id_servicio', [1, 11])
                    ->get();

                foreach ($serviciosPactados as $sp) {
                    $idConcepto = null;
                    $nombreConcepto = '';
                    $montoTotal = $sp->precio_unitario * $sp->cantidad;
                    $detalle = [];

                    if ($sp->id_servicio == 1) {
                        $idConcepto = 5;
                        $nombreConcepto = 'Gasolina Prepago';
                        $detalle = [
                            'km' => null,
                            'monto' => $montoTotal,
                            'litros' => $sp->cantidad,
                            'destino' => null,
                            'precio_litro' => $sp->precio_unitario
                        ];
                    } elseif ($sp->id_servicio == 11) {
                        $idConcepto = 6;
                        $nombreConcepto = 'Servicio de Drop Off';
                        $detalle = [
                            'km' => (string)($reservacion->dropoff_km ?? '0'),
                            'monto' => $montoTotal,
                            'litros' => null,
                            'destino' => ($reservacion->dropoff_ubicacion == 0)
                                ? ($reservacion->dropoff_direccion ?? 'Dirección manual')
                                : 'Ubicación predefinida',
                            'precio_litro' => $reservacion->delivery_precio_km ?? 0
                        ];
                    }

                    if ($idConcepto) {
                        DB::table('cargo_adicional')->insert([
                            'id_contrato' => $idContrato,
                            'id_concepto' => $idConcepto,
                            'concepto'    => $nombreConcepto,
                            'monto'       => $montoTotal,
                            'moneda'      => 'MXN',
                            'detalle'     => json_encode($detalle),
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                }

                DB::table('contrato_evento')->insert([
                    'id_contrato'  => $idContrato,
                    'evento'       => 'Contrato creado automáticamente',
                    'detalle'      => json_encode(['reservacion' => $reservacion->codigo]),
                    'realizado_en' => now(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                $contrato = DB::table('contratos')->where('id_contrato', $idContrato)->first();
            } else {
                // 🛠 Si ya existe contrato y no tiene asesor, ponle el actual
                if ($asesorId && is_null($contrato->id_asesor)) {
                    DB::table('contratos')
                        ->where('id_contrato', $contrato->id_contrato)
                        ->update([
                            'id_asesor'  => $asesorId,
                            'updated_at' => now(),
                        ]);

                    $contrato->id_asesor = $asesorId;
                }
            }

            // =========================================================
            // 3) Asegurar que la reservación tenga id_asesor
            // =========================================================
            if ($asesorId) {
                DB::table('reservaciones')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->update([
                        'id_asesor'  => $asesorId,
                        'updated_at' => now(),
                    ]);
            }

            // =========================================================
            // 4) Catálogos y datos auxiliares (igual que ya tenías)
            // =========================================================

            // Categorías
            $categorias = DB::table('categorias_carros')
                ->select('id_categoria', 'nombre')
                ->orderBy('nombre')
                ->get();

            // Cargos adicionales disponibles
            $cargos_conceptos = DB::table('cargo_concepto')
                ->where('activo', true)
                ->get();

            // Vehículo
            $vehiculo = DB::table('vehiculos')
                ->where('id_vehiculo', $reservacion->id_vehiculo)
                ->first();

            // Seguros paquetes
            $seguros = DB::table('seguro_paquete')
                ->where('activo', true)
                ->select('id_paquete as id_seguro', 'nombre', 'descripcion as cobertura', 'precio_por_dia')
                ->get();

            // Seguro seleccionado (paquete)
            $seguroSeleccionado = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
                ->select('sp.id_paquete as id_seguro', 'sp.nombre', 'sp.precio_por_dia')
                ->where('rps.id_reservacion', $reservacion->id_reservacion)
                ->first();

            // Seguros individuales seleccionados
            $segurosIndividualesSeleccionados = $this->obtenerIndividualesSeleccionados($reservacion->id_reservacion);

            // Protecciones individuales disponibles
            $individuales = DB::table('seguro_individuales')
                ->where('activo', true)
                ->select('id_individual', 'nombre', 'descripcion', 'precio_por_dia')
                ->get();

            // Clasificación por nombre
            $grupo_colision = $individuales->filter(
                fn($i) =>
                str_contains($i->nombre, 'LDW') ||
                    str_contains($i->nombre, 'PDW') ||
                    str_contains($i->nombre, 'CDW') ||
                    str_contains($i->nombre, 'DECLINE')
            );

            $grupo_medicos = $individuales->filter(
                fn($i) =>
                str_contains($i->nombre, 'PAI')
            );

            $grupo_asistencia = $individuales->filter(
                fn($i) =>
                str_contains($i->nombre, 'PRA')
            );

            $grupo_terceros = $individuales->filter(
                fn($i) =>
                str_contains($i->nombre, 'LI')
            );

            $grupo_protecciones = $individuales->filter(
                fn($i) =>
                str_contains($i->nombre, 'LOU') ||
                    str_contains($i->nombre, 'LA')
            );

            view()->share([
                'grupo_colision'     => $grupo_colision,
                'grupo_medicos'      => $grupo_medicos,
                'grupo_asistencia'   => $grupo_asistencia,
                'grupo_terceros'     => $grupo_terceros,
                'grupo_protecciones' => $grupo_protecciones,
            ]);

            // Servicios adicionales
            $servicios = DB::table('servicios')->get();

            // Detectar conductores adicionales
            $servicioConductor = DB::table('servicios')
                ->where('nombre', 'LIKE', '%conductor adicional%')
                ->where('activo', true)
                ->first();

            $conductoresExtras = collect();

            if ($servicioConductor) {
                $adicional = DB::table('reservacion_servicio')
                    ->where('id_reservacion', $reservacion->id_reservacion)
                    ->where('id_servicio', $servicioConductor->id_servicio)
                    ->first();

                if ($adicional && $adicional->cantidad > 0) {
                    for ($i = 1; $i <= $adicional->cantidad; $i++) {
                        $conductoresExtras->push([
                            'id_conductor' => null,
                            'nombres'      => "Conductor adicional $i",
                        ]);
                    }
                }
            }

            // Ubicaciones
            $ubicaciones = DB::table('ubicaciones_servicio')
                ->where('activo', 1)
                ->orderBy('estado')
                ->orderBy('destino')
                ->get();

            // Datos de Delivery
            $delivery = DB::table('reservaciones')
                ->select(
                    'delivery_activo as activo',
                    'delivery_ubicacion as id_ubicacion',
                    'delivery_direccion as direccion',
                    'delivery_km as kms',
                    'delivery_precio_km as precio_km',
                    'delivery_total as total'
                )
                ->where('id_reservacion', $reservacion->id_reservacion)
                ->first();

            // Costo por km de la categoría
            $costoKmCategoria = DB::table('categoria_costo_km')
                ->where('id_categoria', $reservacion->id_categoria)
                ->value('costo_km') ?? 0;
            
            $serviciosReservados = DB::table('reservacion_servicio')
                ->where('id_reservacion', $reservacion->id_reservacion)
                ->pluck('cantidad', 'id_servicio')
                ->toArray();

            $cargosActivos = [];
            if ($contrato) {
                $cargosActivos = DB::table('cargo_adicional')
                    ->where('id_contrato', $contrato->id_contrato)
                    ->pluck('id_concepto')
                    ->toArray();
            }

            // =========================================================
            // 5) Retornar vista
            // =========================================================
            return view('Admin.Contrato', [
                // ... (tus variables existentes)
                'reservacion'        => $reservacion,
                'vehiculo'           => $vehiculo,
                'seguros'            => $seguros,
                'servicios'          => $servicios,
                'seguroSeleccionado' => $seguroSeleccionado,
                'contrato'           => $contrato,
                'cargos_conceptos'   => $cargos_conceptos,
                'conductoresExtras'  => $conductoresExtras,
                'categorias'         => $categorias,
                'ubicaciones'        => $ubicaciones,
                'costoKmCategoria'   => $costoKmCategoria,
                'delivery'           => $delivery,
                'segurosIndividualesSeleccionados' => $segurosIndividualesSeleccionados,
                'individuales'       => $individuales,
                'idReservacion'      => $reservacion->id_reservacion,
                'serviciosReservados' => $serviciosReservados,
                'cargosActivos'       => $cargosActivos,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ContratoController@mostrarContrato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al cargar la reservación.');
        }
    }

    /**
     * ⚙️ Actualiza servicios adicionales seleccionados.
     */
    public function actualizarServicios(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion'  => 'required|integer|exists:reservaciones,id_reservacion',
                'id_servicio'     => 'required|integer|exists:servicios,id_servicio',
                'cantidad'        => 'required|integer|min:0',
                'precio_unitario' => 'required|numeric|min:0',
            ]);

            // 🔹 Buscar el servicio actual
            $servicio = DB::table('servicios')
                ->where('id_servicio', $data['id_servicio'])
                ->first();

            // 🔹 Buscar registro existente en reservacion_servicio
            $existe = DB::table('reservacion_servicio')
                ->where('id_reservacion', $data['id_reservacion'])
                ->where('id_servicio', $data['id_servicio'])
                ->first();

            // =========================================================
            // 🧹 CASO 1: Si la cantidad es 0 → eliminar servicio
            // =========================================================
            if ($data['cantidad'] == 0) {
                if ($existe) {
                    DB::table('reservacion_servicio')->where('id', $existe->id)->delete();
                }

                // 🔁 Si el servicio es "Conductor adicional", eliminar también los conductores del contrato
                if ($servicio && stripos($servicio->nombre, 'conductor adicional') !== false) {
                    $contrato = DB::table('contratos')
                        ->where('id_reservacion', $data['id_reservacion'])
                        ->first();

                    if ($contrato) {
                        DB::table('contrato_conductor_adicional')
                            ->where('id_contrato', $contrato->id_contrato)
                            ->delete();
                    }
                }

                return response()->json([
                    'status' => 'deleted',
                    'msg' => 'Servicio eliminado y sincronizado correctamente.'
                ]);
            }

            // =========================================================
            // ✏️ CASO 2: Actualizar servicio existente
            // =========================================================
            if ($existe) {
                DB::table('reservacion_servicio')
                    ->where('id', $existe->id)
                    ->update([
                        'cantidad'        => $data['cantidad'],
                        'precio_unitario' => $data['precio_unitario'],
                        'updated_at'      => now(),
                    ]);
                $accion = 'updated';
            } else {
                // =========================================================
                // ➕ CASO 3: Insertar servicio nuevo
                // =========================================================
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $data['id_reservacion'],
                    'id_servicio'     => $data['id_servicio'],
                    'cantidad'        => $data['cantidad'],
                    'precio_unitario' => $data['precio_unitario'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $accion = 'inserted';
            }

            // =========================================================
            // 🚗 SINCRONIZAR CONDUCTORES ADICIONALES
            // =========================================================
            if ($servicio && stripos($servicio->nombre, 'conductor adicional') !== false) {
                $contrato = DB::table('contratos')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->first();

                if ($contrato) {
                    $idContrato = $contrato->id_contrato;
                    $cantidadDeseada = $data['cantidad'];

                    $conductoresActuales = DB::table('contrato_conductor_adicional')
                        ->where('id_contrato', $idContrato)
                        ->get();

                    $actualCount = $conductoresActuales->count();

                    // 🧩 Si hay más de los necesarios → eliminar excedentes
                    if ($actualCount > $cantidadDeseada) {
                        $sobrantes = $actualCount - $cantidadDeseada;
                        DB::table('contrato_conductor_adicional')
                            ->where('id_contrato', $idContrato)
                            ->orderByDesc('id_conductor')
                            ->limit($sobrantes)
                            ->delete();
                    }

                    // 🧩 Si hay menos → crear los faltantes
                    if ($actualCount < $cantidadDeseada) {
                        for ($i = $actualCount + 1; $i <= $cantidadDeseada; $i++) {
                            DB::table('contrato_conductor_adicional')->insert([
                                'id_contrato' => $idContrato,
                                'nombres' => "Conductor adicional {$i}",
                                'apellidos' => '',
                                'numero_licencia' => null,
                                'pais_licencia' => null,
                                'fecha_nacimiento' => null,
                                'contacto' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'status' => $accion,
                'msg' => 'Servicio actualizado y sincronizado correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ContratoController@actualizarServicios: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar servicios.'], 500);
        }
    }


    /**
     * 🛡️ Actualiza paquete de seguro seleccionado.
     */
    public function actualizarSeguro(Request $request)
    {
        try {

            // 🔎 Detectar tipo
            $esPaquete    = $request->filled('id_paquete');
            $esIndividual = $request->filled('id_seguro');

            if ($esPaquete === $esIndividual) {
                return response()->json([
                    'error' => 'Debe enviar paquete O seguro individual, no ambos.'
                ], 422);
            }

            // 📋 Validación base
            $base = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            ]);

            $idReservacion = $base['id_reservacion'];

            /* =======================
           🟦 CASO PAQUETE
        ======================= */
            if ($esPaquete) {

                $data = $request->validate([
                    'id_paquete'     => 'required|integer|exists:seguro_paquete,id_paquete',
                    'precio_por_dia' => 'required|numeric|min:0',
                ]);

                // 🔥 Paquete activo → eliminar individuales
                DB::table('reservacion_seguro_individual')
                    ->where('id_reservacion', $idReservacion)
                    ->delete();

                $existe = DB::table('reservacion_paquete_seguro')
                    ->where('id_reservacion', $idReservacion)
                    ->first();

                if ((float)$data['precio_por_dia'] === 0.0) {
                    if ($existe) {
                        DB::table('reservacion_paquete_seguro')
                            ->where('id', $existe->id)
                            ->delete();

                        return response()->json([
                            'status' => 'deleted',
                            'msg' => 'Paquete eliminado correctamente.'
                        ]);
                    }

                    return response()->json([
                        'status' => 'noop',
                        'msg' => 'No existía paquete para eliminar.'
                    ]);
                }

                if ($existe) {
                    DB::table('reservacion_paquete_seguro')
                        ->where('id', $existe->id)
                        ->update([
                            'id_paquete'     => $data['id_paquete'],
                            'precio_por_dia' => $data['precio_por_dia'],
                            'updated_at'     => now(),
                        ]);

                    return response()->json([
                        'status' => 'updated',
                        'msg' => 'Paquete actualizado correctamente.'
                    ]);
                }

                DB::table('reservacion_paquete_seguro')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_paquete'     => $data['id_paquete'],
                    'precio_por_dia' => $data['precio_por_dia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                return response()->json([
                    'status' => 'inserted',
                    'msg' => 'Paquete agregado correctamente.'
                ]);
            }

            /* =======================
           🟨 CASO INDIVIDUAL
        ======================= */
            if ($esIndividual) {

                $data = $request->validate([
                    'id_seguro' => 'required|integer|exists:seguros,id_seguro',
                    'precio'    => 'required|numeric|min:0',
                ]);

                // 🔥 Individual activo → eliminar paquete
                DB::table('reservacion_paquete_seguro')
                    ->where('id_reservacion', $idReservacion)
                    ->delete();

                DB::table('reservacion_seguro_individual')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_seguro'      => $data['id_seguro'],
                    'precio'         => $data['precio'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                return response()->json([
                    'status' => 'inserted',
                    'msg' => 'Seguro individual agregado correctamente.'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error en actualizarSeguro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno al actualizar el seguro.'
            ], 500);
        }
    }

    public function actualizarSegurosIndividuales(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
                'precio_por_dia' => 'required|numeric|min:0',
            ]);

            $idReservacion = $data['id_reservacion'];
            $idIndividual  = $data['id_seguro'];

            // 🔥 1. Si activa un individual → se elimina cualquier paquete
            DB::table('reservacion_paquete_seguro')
                ->where('id_reservacion', $idReservacion)
                ->delete();

            // 🔥 2. Insertar individual si no existe
            $existe = DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $idReservacion)
                ->where('id_individual', $idIndividual)
                ->first();

            if (!$existe) {
                DB::table('reservacion_seguro_individual')->insert([
                    'id_reservacion' => $idReservacion,
                    'id_individual'  => $idIndividual,
                    'precio_por_dia' => $data['precio_por_dia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            return response()->json([
                'ok' => true,
                'status' => 'inserted',
                'msg' => 'Protección individual agregada correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en actualizarSegurosIndividuales: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar.'], 500);
        }
    }

    public function eliminarSeguroIndividual(Request $request)
    {
        try {
            $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
            ]);

            DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $request->id_reservacion)
                ->where('id_individual', $request->id_seguro)
                ->delete();

            return response()->json([
                'ok' => true,
                'status' => 'deleted',
                'msg' => 'Protección individual eliminada.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al eliminar seguro individual: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al eliminar.'], 500);
        }
    }

    public function eliminarTodosLosIndividuales(Request $request)
    {
        try {
            $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            ]);

            DB::table('reservacion_seguro_individual')
                ->where('id_reservacion', $request->id_reservacion)
                ->delete();

            return response()->json([
                'ok' => true,
                'msg' => 'Todos los seguros individuales eliminados.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al borrar todos los individuales: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    private function borrarIndividuales($idReservacion)
    {
        DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $idReservacion)
            ->delete();
    }

    private function borrarPaquete($idReservacion)
    {
        DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $idReservacion)
            ->delete();
    }

    public function guardarDeliveryReservacion(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion'      => 'required|integer|exists:reservaciones,id_reservacion',
                'delivery_activo'     => 'required|boolean',
                'delivery_ubicacion'  => 'nullable|string|max:120',
                'delivery_direccion'  => 'nullable|string|max:255',
                'delivery_km'         => 'nullable|numeric|min:0',
                'delivery_precio_km'  => 'nullable|numeric|min:0',
                'delivery_total'      => 'nullable|numeric|min:0',
            ]);

            // 🔍 Buscar reservación
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if (!$res) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            // 🧹 Si está desactivado → limpiar datos
            if ($data['delivery_activo'] == 0) {

                DB::table('reservaciones')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->update([
                        'delivery_activo'     => 0,
                        'delivery_ubicacion'  => null,
                        'delivery_direccion'  => null,
                        'delivery_km'         => 0,
                        'delivery_precio_km'  => 0,
                        'delivery_total'      => 0,
                        'updated_at'          => now(),
                    ]);

                return response()->json([
                    'status' => 'deleted',
                    'msg' => 'Delivery desactivado correctamente'
                ]);
            }

            // 📝 Guardar delivery activo
            DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->update([
                    'delivery_activo'     => $data['delivery_activo'],
                    'delivery_ubicacion'  => $data['delivery_ubicacion'],
                    'delivery_direccion'  => $data['delivery_direccion'],
                    'delivery_km'         => $data['delivery_km'],
                    'delivery_precio_km'  => $data['delivery_precio_km'],
                    'delivery_total'      => $data['delivery_total'],
                    'updated_at'          => now(),
                ]);

            return response()->json([
                'status' => 'updated',
                'msg'    => 'Delivery guardado correctamente',
                'total'  => $data['delivery_total']
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en guardarDeliveryReservacion: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }


    public function solicitarCambioFecha(Request $request)
    {
        try {

            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'nueva_fecha'    => 'required|date',
                'nueva_hora'     => 'nullable',
                'motivo'         => 'nullable|string|max:500'
            ]);

            // Reservación original
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if (!$res) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            // Crear token único
            $token = bin2hex(random_bytes(32));

            // Guardar solicitud
            DB::table('contrato_cambio_fecha')->insert([
                'id_reservacion'   => $res->id_reservacion,
                'fecha_anterior'   => $res->fecha_inicio,
                'hora_anterior'    => $res->hora_retiro,
                'fecha_solicitada' => $data['nueva_fecha'],
                'hora_solicitada'  => $data['nueva_hora'],
                'motivo'           => $data['motivo'] ?? null,
                'token'            => $token,
                'estado'           => 'pendiente',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            /* ==========================================================
           📧 Enviar correo al superadministrador
        ========================================================== */

            $superadminEmail = "administrador@viajerocarental.com"; // <-- cámbialo

            $linkAprobar  = url("/admin/contrato/cambio-fecha/aprobar/{$token}");
            $linkRechazar = url("/admin/contrato/cambio-fecha/rechazar/{$token}");

            $html = "
            <div style='font-family:sans-serif;'>
                <h2 style='color:#D6121F;'>Solicitud de cambio de fecha</h2>

                <p><b>Reservación:</b> {$res->codigo}</p>
                <p><b>Cliente:</b> {$res->nombre_cliente}</p>

                <p><b>Fecha actual:</b> {$res->fecha_inicio} {$res->hora_retiro}</p>
                <p><b>Nueva fecha solicitada:</b> {$data['nueva_fecha']} {$data['nueva_hora']}</p>

                <p><b>Motivo:</b> " . ($data['motivo'] ?? 'No especificado') . "</p>

                <p>Acciones:</p>

                <p>
                    <a href='{$linkAprobar}'
                       style='background:#16a34a;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Aprobar cambio
                    </a>
                </p>

                <p>
                    <a href='{$linkRechazar}'
                       style='background:#dc2626;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Rechazar solicitud
                    </a>
                </p>
            </div>
        ";

            Mail::html($html, function ($message) use ($superadminEmail) {
                $message->to($superadminEmail)
                    ->subject("Solicitud de cambio de fecha - Viajero Car Rental");
            });

            return response()->json([
                'success' => true,
                'msg'     => 'Solicitud enviada al superadministrador.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en solicitarCambioFecha: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno.'], 500);
        }
    }

    public function aprobarCambioFecha($token)
    {
        try {
            $sol = DB::table('contrato_cambio_fecha')
                ->where('token', $token)
                ->where('estado', 'pendiente')
                ->first();

            if (!$sol) {
                return "Solicitud inválida o ya procesada.";
            }

            // Actualizar la reservación
            DB::table('reservaciones')
                ->where('id_reservacion', $sol->id_reservacion)
                ->update([
                    'fecha_inicio' => $sol->fecha_solicitada,
                    'hora_retiro'  => $sol->hora_solicitada,
                    'aprobado_por_superadmin' => true,
                    'updated_at' => now(),
                ]);

            // 🔄 Recalcular totales con nueva fecha
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $sol->id_reservacion)
                ->first();

            $this->recalcularYActualizarTotales(
                new Request([
                    'fecha_inicio' => $sol->fecha_solicitada,
                    'hora_inicio'  => $sol->hora_solicitada,
                    'fecha_fin'    => $res->fecha_fin,
                    'hora_fin'     => $res->hora_entrega,
                    'id_categoria' => $res->id_categoria,
                ]),
                $sol->id_reservacion
            );



            // Marcar solicitud como aprobada
            DB::table('contrato_cambio_fecha')
                ->where('id', $sol->id)
                ->update([
                    'estado' => 'aprobado',
                    'autorizado_por' => 'superadmin',
                    'fecha_autorizacion' => now()
                ]);

            return "
            <h2 style='font-family:sans-serif;color:#16a34a'>Cambio aprobado ✔</h2>
            <p>La fecha ha sido actualizada exitosamente.</p>
        ";
        } catch (\Throwable $e) {
            Log::error("Error en aprobarCambioFecha: " . $e->getMessage());
            return "Error interno.";
        }
    }

    public function rechazarCambioFecha($token)
    {
        try {
            $sol = DB::table('contrato_cambio_fecha')
                ->where('token', $token)
                ->where('estado', 'pendiente')
                ->first();

            if (!$sol) {
                return "Solicitud inválida o ya procesada.";
            }

            DB::table('contrato_cambio_fecha')
                ->where('id', $sol->id)
                ->update([
                    'estado' => 'rechazado',
                    'autorizado_por' => 'superadmin',
                    'fecha_autorizacion' => now()
                ]);

            return "
            <h2 style='font-family:sans-serif;color:#dc2626'>Cambio rechazado ❌</h2>
            <p>No se realizaron modificaciones en la reservación.</p>
        ";
        } catch (\Throwable $e) {
            Log::error("Error en rechazarCambioFecha: " . $e->getMessage());
            return "Error interno.";
        }
    }

    public function estadoCambioFecha($idReservacion)
    {
        $registro = DB::table('contrato_cambio_fecha')
            ->where('id_reservacion', $idReservacion)
            ->orderBy('id', 'desc')
            ->first();

        if (!$registro) {
            return response()->json(["estado" => "sin-solicitud"]);
        }

        return response()->json([
            "estado" => $registro->estado,
            "fecha_nueva" => $registro->fecha_solicitada
        ]);
    }

    public function recalcularYActualizarTotales(Request $request, $idReservacion)
    {
        try {
            // 1️⃣ Validar datos
            $data = $request->validate([
                'fecha_inicio' => 'required|date',
                'hora_inicio'  => 'nullable',
                'fecha_fin'    => 'required|date',
                'hora_fin'     => 'nullable',
                'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria',
            ]);

            // 2️⃣ Cargar reservación
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$res) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            // 3️⃣ Obtener precio real según reglas
            $categoria = DB::table('categorias_carros')
                ->where('id_categoria', $data['id_categoria'])
                ->first();

            if (!$categoria) {
                return response()->json(['error' => 'Categoría no encontrada'], 404);
            }

            // 📌 PRECIO POR DÍA DEFINITIVO
            if ($res->tarifa_ajustada == 1 && $res->tarifa_modificada > 0) {
                // ⭐ Tarifa personalizada por el operador
                $precioReal = $res->tarifa_modificada;
            } else {
                // ⭐ Precio según la categoría (ideal cuando cambias categoría)
                $precioReal = $categoria->precio_dia;
            }

            // 4️⃣ Calcular días (incluye día inicial)
            $dias = Carbon::parse($data['fecha_inicio'])
                ->diffInDays(Carbon::parse($data['fecha_fin']));


            // 5️⃣ Calcular subtotal, impuestos y total
            $subtotal  = $dias * $precioReal;
            $iva       = $subtotal * 0.16;
            $total     = $subtotal + $iva;

            // 6️⃣ Guardar en DB
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'fecha_inicio' => $data['fecha_inicio'],
                    'hora_retiro'  => $data['hora_inicio'],
                    'fecha_fin'    => $data['fecha_fin'],
                    'hora_entrega' => $data['hora_fin'],
                    'subtotal'     => $subtotal,
                    'impuestos'    => $iva,
                    'total'        => $total,
                    'id_categoria' => $data['id_categoria'],
                    'updated_at'   => now(),
                ]);

            return response()->json([
                'success' => true,
                'dias'    => $dias,
                'precio_dia' => number_format($precioReal, 2),
                'subtotal' => number_format($subtotal, 2),
                'impuestos' => number_format($iva, 2),
                'total'    => number_format($total, 2),
                'total_formateado' => number_format($total, 2),
                'moneda' => $res->moneda,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error en recalcularYActualizarTotales: " . $e->getMessage());
            return response()->json(['error' => 'Error interno.'], 500);
        }
    }

    /**
     * 🚗 Obtener vehículos disponibles por categoría
     * Usado por el modal del paso 1 del contrato.
     */
    public function vehiculosPorCategoria($idCategoria)
    {
        try {

            Log::info("🔍 Buscando vehículos para categoría: $idCategoria");

            $vehiculos = DB::table('vehiculos as v')
                ->leftJoin('vehiculo_imagenes as img', function ($join) {
                    $join->on('img.id_vehiculo', '=', 'v.id_vehiculo')
                        ->where('img.orden', 0);
                })
                ->leftJoin('mantenimientos as m', 'm.id_vehiculo', '=', 'v.id_vehiculo')
                ->select(
                    'v.id_vehiculo',
                    'v.nombre_publico',
                    'v.marca',
                    'v.modelo',
                    'v.color',
                    'v.transmision',
                    'v.asientos',
                    'v.puertas',
                    'v.numero_serie',
                    'v.placa',
                    'v.kilometraje',
                    'v.gasolina_actual',
                    'v.fin_vigencia_poliza',
                    'img.url as foto_url',

                    // mantenimiento
                    'm.kilometraje_actual',
                    'm.proximo_servicio'
                )
                ->where('v.id_categoria', $idCategoria)
                ->orderBy('v.marca')
                ->orderBy('v.modelo')
                ->get();

            // Procesar km restantes + color
            $vehiculos->transform(function ($v) {

                // calcular km restantes
                if ($v->proximo_servicio && $v->kilometraje) {
                    $v->km_restantes = $v->proximo_servicio - $v->kilometraje;
                } else {
                    $v->km_restantes = null;
                }

                // color
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

            return response()->json([
                "success" => true,
                "data" => $vehiculos
            ]);
        } catch (\Throwable $e) {

            Log::error("❌ ERROR vehiculosPorCategoria: " . $e->getMessage());

            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarCategoria(Request $request, $idReservacion)
    {
        try {
            $data = $request->validate([
                'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria'
            ]);

            // 1️⃣ Cargar reservación actual
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$res) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Reservación no encontrada.'
                ], 404);
            }

            // 2️⃣ Cargar categoría nueva para sacar tarifa base real
            $categoria = DB::table('categorias_carros')
                ->where('id_categoria', $data['id_categoria'])
                ->first();

            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Categoría no encontrada.'
                ], 404);
            }

            // 3️⃣ Flags para el frontend
            $vehiculoRemovido = !is_null($res->id_vehiculo);
            $tarifaLimpiada   = ($res->tarifa_ajustada == 1) || (!is_null($res->tarifa_modificada) && $res->tarifa_modificada > 0);

            // 4️⃣ Actualizar reservación según tu flujo C
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    // Categoría nueva
                    'id_categoria'     => $data['id_categoria'],

                    // Siempre quitar vehículo al cambiar categoría (opción C)
                    'id_vehiculo'      => null,

                    // Reset total de tarifa modificada
                    'tarifa_ajustada'  => 0,
                    'tarifa_modificada' => null,

                    // Fijar nueva tarifa base del catálogo
                    'tarifa_base'      => $categoria->precio_dia,

                    'updated_at'       => now(),
                ]);

            // return response()->json([
            //     'success'          => true,
            //     'msg'              => 'Categoría actualizada correctamente.',
            //     'vehiculo_removido' => $vehiculoRemovido,
            //     'tarifa_limpiada'  => $tarifaLimpiada,
            //     'tarifa_base_nueva' => number_format($categoria->precio_dia, 2),
            // ]);
            return $this->resumenContrato($idReservacion);
        } catch (\Throwable $e) {
            Log::error("Error en actualizarCategoria: " . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Error interno al guardar la categoría.'
            ], 500);
        }
    }

    public function obtenerOfertaUpgrade($idReservacion)
    {
        try {
            // 1️⃣ Reservación
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$res) {
                return response()->json(['success' => false, 'error' => 'Reservación no encontrada']);
            }

            // 2️⃣ Categoría actual
            $catActual = DB::table('categorias_carros')
                ->where('id_categoria', $res->id_categoria)
                ->first();

            if (!$catActual) {
                return response()->json(['success' => false, 'error' => 'Categoría actual no encontrada']);
            }

            // 🟦 ORDEN OFICIAL (DEBES AJUSTARLO SI CAMBIA)
            $orden = ["C", "D", "E", "F", "IC", "I", "IB", "M", "L", "H", "HI"];

            // 🟩 posición actual
            $posActual = array_search($catActual->codigo, $orden);

            if ($posActual === false) {
                return response()->json(['success' => false, 'msg' => 'Categoría actual no está en el orden oficial.']);
            }

            // 3️⃣ Conseguir TODAS las categorías superiores
            $codigosSuperiores = array_slice($orden, $posActual + 1);

            if (empty($codigosSuperiores)) {
                return response()->json(['success' => false, 'msg' => 'No hay categorías superiores disponibles.']);
            }

            // 4️⃣ Obtener esas categorías desde DB
            $categorias = DB::table('categorias_carros')
                ->whereIn('codigo', $codigosSuperiores)
                ->orderBy('precio_dia', 'asc')
                ->get();

            if ($categorias->isEmpty()) {
                return response()->json(['success' => false, 'msg' => 'No hay categorías superiores en DB.']);
            }

            // 5️⃣ Seleccionar UNA categoría random
            $catSuperior = $categorias->random();

            // 6️⃣ Vehículo random
            $vehiculo = DB::table('vehiculos')
                ->where('id_categoria', $catSuperior->id_categoria)
                ->inRandomOrder()
                ->first();

            if (!$vehiculo) {
                return response()->json(['success' => false, 'msg' => 'No hay vehículos disponibles para upgrade.']);
            }

            // 7️⃣ Imagen del vehículo
            $foto = DB::table('vehiculo_imagenes')
                ->where('id_vehiculo', $vehiculo->id_vehiculo)
                ->orderBy('orden', 'asc')
                ->value('url');

            $fotoFinal = $foto ?? '/img/default-car.jpg';

            // ⭐ PRECIOS
            $precioReal    = $catSuperior->precio_dia;
            $precioInflado = round($precioReal * 1.35, 2);
            $descuento     = rand(55, 75);
            $precioFinal   = round($precioInflado * (1 - ($descuento / 100)), 2);

            // ⭐ RESPUESTA COMPLETA AL FRONT
            return response()->json([
                'success' => true,
                'categoria' => [
                    'id_categoria'     => $catSuperior->id_categoria,
                    'codigo'           => $catSuperior->codigo,
                    'nombre'           => $catSuperior->nombre,
                    'descripcion'      => $catSuperior->descripcion,

                    'precio_real'      => $precioReal,
                    'precio_inflado'   => $precioInflado,
                    'descuento'        => $descuento,
                    'precio_final'     => $precioFinal,

                    'imagen'           => $fotoFinal,
                    'nombre_vehiculo'  => $vehiculo->nombre_publico,
                    'transmision'      => $vehiculo->transmision,
                    'asientos'         => $vehiculo->asientos,
                    'puertas'          => $vehiculo->puertas,
                    'color'            => $vehiculo->color,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Error obtenerOfertaUpgrade: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error interno'], 500);
        }
    }

    public function categoriaInfo($codigo)
    {
        try {
            $cat = DB::table('categorias_carros')
                ->where('codigo', $codigo)
                ->first();

            if (!$cat) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Categoría no encontrada'
                ], 404);
            }

            return response()->json([
                'success'   => true,
                'categoria' => $cat
            ]);
        } catch (\Throwable $e) {
            Log::error("Error categoriaInfo: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Error interno'
            ], 500);
        }
    }

    public function registrarPago(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_contrato'    => 'required|integer|exists:contratos,id_contrato',
                'metodo'         => 'required|string|max:50',
                'tipo_pago'      => 'required|string|max:50',
                'monto'          => 'required|numeric|min:0.01',
                'notas'          => 'nullable|string',
                'extra_datos'    => 'nullable|array'
            ]);

            // Insertar pago
            DB::table('pagos')->insert([
                'id_reservacion'      => $data['id_reservacion'],
                'id_contrato'         => $data['id_contrato'],
                'metodo'              => $data['metodo'],
                'tipo_pago'           => $data['tipo_pago'],
                'monto'               => $data['monto'],
                'estatus'             => 'paid',
                'moneda'              => 'MXN',
                'payload_webhook'     => json_encode($data['extra_datos'] ?? null),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            return response()->json([
                'success' => true,
                'msg'     => 'Pago registrado correctamente'
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR registrarPago: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al registrar pago'], 500);
        }
    }

    public function eliminarPago($idPago)
    {
        try {
            DB::table('pagos')->where('id_pago', $idPago)->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error("Error eliminarPago: " . $e->getMessage());
            return response()->json(['ok' => false], 500);
        }
    }
    public function pagoPayPal(Request $req)
    {
        $req->validate([
            'id_reservacion' => 'required|integer',
            'order_id'       => 'required|string',
            'monto'          => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $req->id_reservacion)
                ->first();

            if (!$res) {
                return response()->json(['ok' => false, 'msg' => 'Reservación no encontrada'], 404);
            }

            // Crear el registro de pago
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion'       => $req->id_reservacion,
                'id_contrato'          => null,

                'origen_pago'          => 'online',
                'pasarela'             => 'paypal',
                'referencia_pasarela'  => $req->order_id,

                'estatus'              => 'paid',
                'metodo'               => 'PayPal',
                'tipo_pago'            => 'PAGO RESERVACIÓN',

                'monto'                => $req->monto,
                'moneda'               => 'MXN',

                'payload_webhook'      => null,
                'captured_at'          => now(),

                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Actualizar reservación
            DB::table('reservaciones')
                ->where('id_reservacion', $req->id_reservacion)
                ->update([
                    'paypal_order_id' => $req->order_id,
                    'status_pago'     => 'Pagado',
                    'metodo_pago'     => 'en línea',
                    'estado'          => 'confirmada',
                    'updated_at'      => now(),
                ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'msg' => 'Pago registrado',
                'id_pago' => $idPago
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => $th->getMessage()]);
        }
    }
    public function pagoManual(Request $req)
    {
        $req->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'tipo_pago'      => 'required|string|max:50',
            'metodo'         => 'required|string|max:50',
            'monto'          => 'required|numeric|min:1',
            'notas'          => 'nullable|string|max:500',
            'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        DB::beginTransaction();

        try {
            // ---------------------------------------------------
            // 1) Determinar ORIGEN DEL PAGO según el método
            // ---------------------------------------------------
            $origen = match (strtoupper($req->metodo)) {
                'EFECTIVO'         => 'mostrador',
                'TRANSFERENCIA',
                'SPEI',
                'DEPOSITO'         => 'mostrador',
                'VISA',
                'MASTERCARD',
                'AMEX',
                'DEBITO'           => 'terminal',
                default            => 'mostrador',
            };

            // ---------------------------------------------------
            // 2) Subir comprobante SI existe
            // ---------------------------------------------------
            $filePath = null;

            if ($req->hasFile('comprobante')) {
                $filePath = $req->file('comprobante')->store('pagos', 'public');
            }

            // ---------------------------------------------------
            // 3) Insertar el pago manual
            // ---------------------------------------------------
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion' => $req->id_reservacion,
                'id_contrato'    => null,

                'origen_pago' => $origen,
                'metodo'      => strtoupper($req->metodo),
                'tipo_pago'   => strtoupper($req->tipo_pago),

                'monto'       => $req->monto,
                'moneda'      => 'MXN',
                'estatus'     => 'paid',

                'comprobante' => $filePath,
                'pasarela'    => null,
                'referencia_pasarela' => null,

                'payload_webhook' => json_encode([
                    'notas' => $req->notas,
                ]),

                'captured_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // ---------------------------------------------------
            // 4) Verificar si queda saldo pendiente y actualizar reservación
            // ---------------------------------------------------
            $res = DB::table('reservaciones')->where('id_reservacion', $req->id_reservacion)->first();

            $pagado = DB::table('pagos')
                ->where('id_reservacion', $req->id_reservacion)
                ->where('estatus', 'paid')
                ->sum('monto');

            $saldo = $res->total - $pagado;

            if ($saldo <= 0) {
                DB::table('reservaciones')
                    ->where('id_reservacion', $req->id_reservacion)
                    ->update([
                        'status_pago' => 'Pagado',
                        'metodo_pago' => $origen,
                        'estado'      => 'confirmada',
                        'updated_at'  => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'id_pago' => $idPago,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error pagoManual: " . $th->getMessage());

            return response()->json([
                'ok' => false,
                'msg' => 'Error interno al registrar el pago',
            ], 500);
        }
    }


    public function pagoEfectivo(Request $req)
    {
        $req->validate([
            'id_reservacion' => 'required|integer',
            'tipo_pago'      => 'required|string',
            'monto'          => 'required|numeric|min:1',
            'notas'          => 'nullable|string|max:500',
        ]);

        $idPago = DB::table('pagos')->insertGetId([
            'id_reservacion' => $req->id_reservacion,

            'origen_pago' => 'mostrador',
            'metodo'      => 'EFECTIVO',
            'tipo_pago'   => $req->tipo_pago,
            'monto'       => $req->monto,
            'moneda'      => 'MXN',

            'estatus'     => 'paid',
            'pasarela'    => null,
            'referencia_pasarela' => null,

            'payload_webhook' => json_encode([
                'notas' => $req->notas,
            ]),

            'captured_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['ok' => true, 'id_pago' => $idPago]);
    }

    public function pagoTerminal(Request $req)
    {
        $req->validate([
            'id_reservacion' => 'required|integer',
            'tipo_pago'      => 'required|string',
            'metodo'         => 'required|string', // VISA, MASTERCARD, AMEX, DEBITO
            'monto'          => 'required|numeric|min:1',
            'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        // Guardar ticket
        $filePath = $req->file('comprobante')->store('pagos', 'public');

        $idPago = DB::table('pagos')->insertGetId([
            'id_reservacion' => $req->id_reservacion,

            'origen_pago' => 'terminal',
            'metodo'      => $req->metodo,
            'tipo_pago'   => $req->tipo_pago,

            'monto'       => $req->monto,
            'moneda'      => 'MXN',

            'estatus'     => 'paid',
            'comprobante' => $filePath,

            'pasarela'    => null,
            'referencia_pasarela' => null,

            'payload_webhook' => null,
            'captured_at'     => now(),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'id_pago' => $idPago]);
    }
    public function pagoTransferencia(Request $req)
    {
        $req->validate([
            'id_reservacion' => 'required|integer',
            'tipo_pago'      => 'required|string',
            'metodo'         => 'required|string', // TRANSFERENCIA / SPEI / DEPOSITO
            'monto'          => 'required|numeric|min:1',
            'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
            'notas'          => 'nullable|string|max:500',
        ]);

        $filePath = $req->file('comprobante')->store('pagos', 'public');

        $idPago = DB::table('pagos')->insertGetId([
            'id_reservacion' => $req->id_reservacion,

            'origen_pago' => 'mostrador',
            'metodo'      => $req->metodo,
            'tipo_pago'   => $req->tipo_pago,

            'monto'       => $req->monto,
            'moneda'      => 'MXN',

            'estatus'     => 'paid',
            'comprobante' => $filePath,

            'pasarela'    => null,
            'referencia_pasarela' => null,

            'payload_webhook' => json_encode([
                'notas' => $req->notas,
            ]),

            'captured_at' => now(),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'id_pago' => $idPago]);
    }

    // private function calcularSaldoPendiente($id)
    // {
    //     $res = DB::table('reservaciones')->where('id_reservacion', $id)->first();
    //     if (!$res) return 0;

    //     $pagos = DB::table('pagos')
    //         ->where('id_reservacion', $id)
    //         ->where('estatus', 'paid')
    //         ->sum('monto');

    //     return ($res->total - $pagos);
    // }

}
