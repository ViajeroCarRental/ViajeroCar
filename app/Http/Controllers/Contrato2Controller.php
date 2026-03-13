<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class Contrato2Controller extends ContratoBaseController
{
    public function mostrarContrato2($id)
    {
        try {
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
                ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria', '=', 'cck.id_categoria')
                ->where('r.id_reservacion', $id)
                ->orWhere('c.id_contrato', $id)
                ->select(
                    'r.*',
                    'c.id_contrato',
                    'c.numero_contrato',
                    'sr.nombre as sucursal_retiro_nombre',
                    'se.nombre as sucursal_entrega_nombre',
                    'v.id_vehiculo',
                    'v.marca',
                    'v.modelo',
                    'v.placa',
                    'v.color',
                    'v.transmision',
                    'v.asientos',
                    'v.puertas',
                    'cck.costo_km as costo_km_cat'
                )
                ->first();

            if (!$reservacion) {
                abort(404, 'No se encontró la reservación con el ID: ' . $id);
            }

            $idReservacion = $reservacion->id_reservacion;
            $idContrato = $reservacion->id_contrato;

            if (!$idContrato) {
                $numeroContrato = 'CTR-' . strtoupper(bin2hex(random_bytes(4)));
                $idContrato = DB::table('contratos')->insertGetId([
                    'id_reservacion'  => $idReservacion,
                    'id_asesor'       => session('id_usuario') ?? null,
                    'numero_contrato' => $numeroContrato,
                    'estado'          => 'abierto',
                    'abierto_en'      => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $reservacion->id_contrato = $idContrato;
                $reservacion->numero_contrato = $numeroContrato;
            }

            $ubicaciones = Cache::remember('ubicaciones_servicio', 86400, function () {
                return DB::table('ubicaciones_servicio')
                    ->select('id_ubicacion', 'estado', 'destino', 'km')
                    ->where('activo', 1)->get();
            });

            $conceptos = Cache::remember('cargo_concepto_filtrado', 86400, function () {
                return DB::table('cargo_concepto')
                    ->select('id_concepto', 'nombre', 'monto_base', 'descripcion', 'moneda')
                    ->where('activo', true)
                    ->whereNotIn('id_concepto', [5, 6])
                    ->get();
            });

            $categorias = Cache::remember('categorias_carros', 3600, function () {
                return DB::table('categorias_carros')
                    ->select('id_categoria', 'nombre', 'codigo', 'precio_dia')
                    ->orderBy('nombre')
                    ->get();
            });

            $serviciosReservados = DB::table('reservacion_servicio')
                ->where('id_reservacion', $idReservacion)
                ->pluck('cantidad', 'id_servicio')
                ->toArray();

            $cargosDelContrato = DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->get();

            $cargosActivosIds = $cargosDelContrato->pluck('id_concepto')->toArray();
            $cargoGas         = $cargosDelContrato->where('id_concepto', 5)->first();
            $cargoDrop        = $cargosDelContrato->where('id_concepto', 6)->first();
            $totalPaso4Server = $cargosDelContrato->sum('monto');

            $conductoresExtras = collect();
            $idServicioConductor = 4; // Conductor Adicional

            if (isset($serviciosReservados[$idServicioConductor])) {
                $cantidad = $serviciosReservados[$idServicioConductor];
                for ($i = 1; $i <= $cantidad; $i++) {
                    // Buscamos si ya tiene registro físico en la tabla de conductores
                    $conductorDb = DB::table('contrato_conductor_adicional')
                        ->where('id_contrato', $idContrato)
                        ->skip($i - 1)->first();

                    $conductoresExtras->push([
                        'id_conductor' => $conductorDb->id_conductor ?? null,
                        'nombres' => $conductorDb->nombres ?? "Conductor adicional $i"
                    ]);
                }
            }

            $idServicioMenor = Cache::remember('id_servicio_menor', 86400, function () {
                return DB::table('servicios')->where('nombre', 'LIKE', '%menor%')->value('id_servicio');
            });

            return view('Admin.Contrato2', [
                'reservacion'         => $reservacion,
                'idReservacion'       => $idReservacion,
                'contrato'            => $reservacion,
                'idContrato'          => $idContrato,
                'vehiculo'            => $reservacion,
                'ubicaciones'         => $ubicaciones,
                'cargos_conceptos'    => $conceptos,
                'categorias'          => $categorias,
                'costoKmCategoria'    => $reservacion->costo_km_cat ?? 0,
                'conductoresExtras'   => $conductoresExtras,
                'serviciosReservados' => $serviciosReservados,
                'idServicioMenor'     => $idServicioMenor ?? 0,
                'delivery' => (object)[
                    'activo'       => $reservacion->delivery_activo,
                    'id_ubicacion' => $reservacion->delivery_ubicacion,
                    'direccion'    => $reservacion->delivery_direccion,
                    'kms'          => $reservacion->delivery_km,
                    'total'        => $reservacion->delivery_total
                ],
                'cargosActivos'    => $cargosActivosIds,
                'cargoGas'         => $cargoGas,
                'cargoDrop'        => $cargoDrop,
                'totalPaso4Server' => $totalPaso4Server,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en mostrarContrato2: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el contrato.');
        }
    }

    /**
     * 🟢 NUEVO: Activa o desactiva servicios adicionales (como el Menor de Edad).
     * Se guarda en reservacion_servicio y lee el catálogo de servicios.
     */
    public function actualizarServiciosExtras(Request $request)
    {
        try {
            $idReservacion = $request->id_reservacion;
            $idServicio = $request->id_servicio;
            $forzar = $request->forzar;
            $cantidad = $request->cantidad ?? 1; // <--- Recibimos la cantidad del JS

            $query = DB::table('reservacion_servicio')
                ->where('id_reservacion', $idReservacion)
                ->where('id_servicio', $idServicio);

            if ($forzar === 'off' || $cantidad <= 0) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            $servicioDb = DB::table('servicios')->where('id_servicio', $idServicio)->first();
            $precio = $servicioDb->precio ?? 0;

            if ($query->exists()) {
                // ACTUALIZAMOS LA CANTIDAD (Si hay 2 menores, cantidad = 2)
                $query->update([
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'updated_at' => now()
                ]);
            } else {
                // INSERTAMOS NUEVO
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $idReservacion,
                    'id_servicio'     => $idServicio,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
    /**
     * 💰 Activa o desactiva cargos adicionales (toggle ON/OFF).
     */
    public function actualizarCargos(Request $request)
    {
        try {
            $idContrato = !empty($request->id_contrato) ? $request->id_contrato : null;
            $idConcepto = $request->id_concepto;

            // 🟢 NUEVO: Recibe la orden estricta del JavaScript
            $forzar = $request->forzar ?? null;

            if (!$idContrato || !$idConcepto) {
                return response()->json(['success' => false, 'msg' => 'Falta el ID del contrato o el concepto.']);
            }

            $query = DB::table('cargo_adicional')
                ->where('id_concepto', $idConcepto)
                ->where('id_contrato', $idContrato);

            $existe = $query->exists();

            // ==========================================
            // 🟢 LÓGICA ESTRICTA (Ignora el toggle si recibe orden)
            // ==========================================
            if ($forzar === 'off') {
                if ($existe) {
                    $query->delete();
                }
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            if ($forzar === 'on') {
                if (!$existe) {
                    $conceptoDb = DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->first()
                        ?? DB::table('cargo_concepto')->where('id', $idConcepto)->first();

                    DB::table('cargo_adicional')->insert([
                        'id_contrato'    => $idContrato,
                        'id_concepto'    => $idConcepto,
                        'concepto'       => $conceptoDb->nombre ?? 'Cargo adicional',
                        'monto'          => $conceptoDb->monto_base ?? 0
                    ]);
                }
                return response()->json(['success' => true, 'action' => 'inserted']);
            }

            // ==========================================
            // 🟢 LÓGICA TOGGLE ORIGINAL (Para clics manuales del Paso 4)
            // ==========================================
            if ($existe) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            } else {
                $conceptoDb = DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->first()
                    ?? DB::table('cargo_concepto')->where('id', $idConcepto)->first();

                DB::table('cargo_adicional')->insert([
                    'id_contrato'    => $idContrato,
                    'id_concepto'    => $idConcepto,
                    'concepto'       => $conceptoDb->nombre ?? 'Cargo adicional',
                    'monto'          => $conceptoDb->monto_base ?? 0
                ]);
                return response()->json(['success' => true, 'action' => 'inserted']);
            }
        } catch (\Exception $e) {
            Log::error("ERROR actualizarCargos: " . $e->getMessage());
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function obtenerCargosContrato($idContrato)
    {
        try {
            $cargos = DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->select('id_concepto', 'monto', 'detalle')
                ->get()
                ->map(function ($c) {
                    $c->detalle = $c->detalle ? json_decode($c->detalle) : null;
                    return $c;
                });

            return response()->json([
                'success' => true,
                'cargos'  => $cargos
            ]);
        } catch (\Throwable $e) {
            Log::error("ERROR obtenerCargosContrato: " . $e->getMessage());
            return response()->json([
                'success' => false
            ], 500);
        }
    }

    public function guardarCargoVariable(Request $request)
    {
        try {
            $idContrato = $request->id_contrato;
            $idConcepto = $request->id_concepto;

            if (!$idContrato || !$idConcepto) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Datos incompletos'
                ]);
            }

            // ======================================
            // 🔥 DATOS VARIABLES RECIBIDOS
            // ======================================
            $montoVariable = $request->monto_variable ?? 0;
            $kilometros    = $request->km ?? null;
            $destino       = $request->destino ?? null;
            $litros        = $request->litros ?? null;
            $precioLitro   = $request->precio_litro ?? null;

            // Guardar JSON único (genérico)
            $json = [
                'km'           => $kilometros,
                'destino'      => $destino,
                'litros'       => $litros,
                'precio_litro' => $precioLitro,
                'monto'        => $montoVariable,
            ];

            // Buscar si ya existe
            $existe = DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->where('id_concepto', $idConcepto)
                ->first();

            // ======================================
            // 🔥 SI MONTO ES 0 → BORRAR
            // ======================================
            if ($montoVariable == 0) {
                DB::table('cargo_adicional')
                    ->where('id_contrato', $idContrato)
                    ->where('id_concepto', $idConcepto)
                    ->delete();

                return response()->json([
                    'success' => true,
                    'action'  => 'deleted',
                    'msg'     => 'Cargo eliminado'
                ]);
            }

            // ======================================
            // 🔄 SI EXISTE → UPDATE
            // ======================================
            if ($existe) {
                DB::table('cargo_adicional')
                    ->where('id_contrato', $idContrato)
                    ->where('id_concepto', $idConcepto)
                    ->update([
                        'monto'      => $montoVariable,
                        'detalle'      => json_encode($json),
                        'updated_at' => now()
                    ]);

                return response()->json([
                    'success' => true,
                    'action'  => 'updated',
                    'msg'     => 'Cargo actualizado'
                ]);
            }

            // ======================================
            // ➕ SI NO EXISTE → INSERT
            // ======================================
            DB::table('cargo_adicional')->insert([
                'id_contrato' => $idContrato,
                'id_concepto' => $idConcepto,
                'concepto'    => DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->value('nombre'),
                'monto'       => $montoVariable,
                'detalle'       => json_encode($json),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'action'  => 'inserted',
                'msg'     => 'Cargo variable guardado'
            ]);
        } catch (\Exception $e) {
            Log::error("ERROR guardarCargoVariable Paso 4: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg'     => 'Error en servidor'
            ]);
        }
    }

    public function asignarVehiculo(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'id_vehiculo'    => 'required|integer|exists:vehiculos,id_vehiculo',
            ]);

            DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->update([
                    'id_vehiculo' => $data['id_vehiculo'],
                    'updated_at'  => now(),
                ]);

            // 🔹 Obtener vehículo asignado
            $vehiculo = DB::table('vehiculos')
                ->where('id_vehiculo', $data['id_vehiculo'])
                ->first();

            return response()->json([
                'success' => true,
                'msg'     => 'Vehículo asignado correctamente.',
                'vehiculo' => $vehiculo
            ]);
        } catch (\Throwable $e) {
            Log::error("Error asignando vehículo: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'Error interno'
            ], 500);
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

    public function aceptarUpgrade(Request $request, $idReservacion)
    {
        try {
            $data = $request->validate([
                'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria'
            ]);

            // ⚙️ Categoría nueva
            $cat = DB::table('categorias_carros')
                ->where('id_categoria', $data['id_categoria'])
                ->first();

            if (!$cat) {
                return response()->json(['error' => 'Categoría no encontrada'], 404);
            }

            // 🔄 Aplicar upgrade
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'id_categoria'      => $cat->id_categoria,
                    'tarifa_base'       => $cat->precio_dia,
                    'tarifa_ajustada'   => 0,
                    'tarifa_modificada' => null,
                    'id_vehiculo'       => null,
                    'updated_at'        => now(),
                ]);

            return response()->json([
                'success' => true,
                'msg'     => 'Upgrade aplicado correctamente.',
                'tarifa_base' => number_format($cat->precio_dia, 2)
            ]);
        } catch (\Throwable $e) {
            Log::error("Error aceptarUpgrade: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function rechazarUpgrade($idReservacion)
    {
        try {
            DB::table('contrato_evento')->insert([
                'id_contrato'  => DB::table('contratos')->where('id_reservacion', $idReservacion)->value('id_contrato'),
                'evento'       => 'Upgrade rechazado',
                'detalle'      => json_encode([]),
                'realizado_en' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json([
                'success' => true,
                'msg'     => 'Oferta rechazada.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error rechazarUpgrade: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    /**
     * 📄 Guarda documentación de identificación y licencia
     */
    // public function guardarDocumentacion(Request $request)
    // {
    //     try {

    //         // ============================
    //         // 1. VALIDACIÓN DE DATOS
    //         // ============================
    //         $data = $request->validate([
    //             'id_contrato'   => 'required|integer|exists:contratos,id_contrato',

    //             // Identificación
    //             'tipo_identificacion' => 'nullable|string|max:50',
    //             'numero_identificacion' => 'nullable|string|max:50',
    //             'nombre' => 'nullable|string|max:100',
    //             'apellido_paterno' => 'nullable|string|max:100',
    //             'apellido_materno' => 'nullable|string|max:100',
    //             'fecha_nacimiento' => 'nullable|date',
    //             'fecha_vencimiento_id' => 'nullable|date',

    //             // Contacto de emergencia
    //             'contacto_emergencia' => 'nullable|string|max:120',

    //             // Licencia
    //             'numero_licencia' => 'nullable|string|max:80',
    //             'emite_licencia' => 'nullable|string|max:100', // viene del HTML
    //             'fecha_emision_licencia' => 'nullable|date',
    //             'fecha_vencimiento_licencia' => 'nullable|date',

    //             // Conductor adicional (si aplica)
    //             'id_conductor' => 'nullable|integer|exists:contrato_conductor_adicional,id_conductor',

    //             // Archivos (MULTIPLATAFORMA)
    //             'idFrente'  => 'nullable|file|max:20480',
    //             'idReverso' => 'nullable|file|max:20480',
    //             'licFrente' => 'nullable|file|max:20480',
    //             'licReverso' => 'nullable|file|max:20480',

    //         ]);

    //         $idContrato   = $data['id_contrato'];
    //         $idConductor  = $data['id_conductor'] ?? null;
    //         // ============================
    //         // 1.1 VALIDACIÓN REAL DE ARCHIVOS (MULTIPLATAFORMA)
    //         // ============================
    //         $archivos = [
    //             'idFrente'  => $request->file('idFrente'),
    //             'idReverso' => $request->file('idReverso'),
    //             'licFrente' => $request->file('licFrente'),
    //             'licReverso' => $request->file('licReverso'),
    //         ];

    //         foreach ($archivos as $campo => $file) {
    //             if ($file) {
    //                 $mime = $file->getMimeType();

    //                 if (!str_starts_with($mime, 'image/')) {
    //                     throw ValidationException::withMessages([
    //                         $campo => "Tipo de archivo no permitido: $mime"
    //                     ]);
    //                 }
    //             }
    //         }



    //         // ============================
    //         // 2. FUNCIÓN PARA GUARDAR FOTO
    //         // ============================
    //         $guardarImagen = function ($file) {
    //             if (!$file) return null;

    //             return DB::table('archivos')->insertGetId([
    //                 'nombre_original' => $file->getClientOriginalName(),
    //                 'tipo'            => 'imagen',
    //                 'contenido'       => file_get_contents($file->getRealPath()), // LONGBLOB real
    //                 'extension'       => $file->extension(),
    //                 'mime_type'       => $file->getMimeType(),
    //                 'tamano_bytes'    => $file->getSize(),
    //                 'created_at'      => now(),
    //                 'updated_at'      => now(),
    //             ]);
    //         };

    //         // Guardar imágenes
    //         $idArchivoFrente = $guardarImagen($request->file('idFrente'));
    //         $idArchivoReverso = $guardarImagen($request->file('idReverso'));
    //         $idLicFrente      = $guardarImagen($request->file('licFrente'));
    //         $idLicReverso     = $guardarImagen($request->file('licReverso'));


    //         // ============================
    //         // 3. CREAR CONDUCTOR ADICIONAL
    //         // ============================
    //         if (empty($idConductor) && !empty($data['nombre'])) {

    //             $idConductor = DB::table('contrato_conductor_adicional')->insertGetId([
    //                 'id_contrato'      => $idContrato,
    //                 'nombres'          => $data['nombre'],
    //                 'apellidos'        => trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
    //                 'numero_licencia'  => $data['numero_licencia'] ?? null,
    //                 'pais_licencia'    => $data['emite_licencia'] ?? null,
    //                 'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
    //                 'contacto'         => $data['contacto_emergencia'] ?? null, // NUEVO
    //                 'created_at'       => now(),
    //                 'updated_at'       => now(),
    //             ]);

    //             DB::table('contrato_evento')->insert([
    //                 'id_contrato' => $idContrato,
    //                 'evento'      => 'Conductor adicional registrado automáticamente',
    //                 'detalle'     => json_encode([
    //                     'nombre'   => $data['nombre'],
    //                     'licencia' => $data['numero_licencia'] ?? 'N/A'
    //                 ]),
    //                 'realizado_en' => now(),
    //                 'created_at'   => now(),
    //                 'updated_at'   => now(),
    //             ]);
    //         }


    //         // ============================
    //         // 4. DOCUMENTO: IDENTIFICACIÓN
    //         // ============================
    //         DB::table('contrato_documento')->insert([
    //             'id_contrato'        => $idContrato,
    //             'id_conductor'       => $idConductor,
    //             'tipo'               => 'identificacion',

    //             'tipo_identificacion' => $data['tipo_identificacion'] ?? null,
    //             'numero_identificacion' => $data['numero_identificacion'] ?? null,
    //             'nombre'             => $data['nombre'] ?? null,
    //             'apellido_paterno'   => $data['apellido_paterno'] ?? null,
    //             'apellido_materno'   => $data['apellido_materno'] ?? null,
    //             'fecha_nacimiento'   => $data['fecha_nacimiento'] ?? null,
    //             'fecha_vencimiento'  => $data['fecha_vencimiento_id'] ?? null,

    //             'id_archivo_frente'  => $idArchivoFrente,
    //             'id_archivo_reverso' => $idArchivoReverso,

    //             'created_at'         => now(),
    //             'updated_at'         => now(),
    //         ]);


    //         // ============================
    //         // 5. DOCUMENTO: LICENCIA
    //         // ============================
    //         DB::table('contrato_documento')->insert([
    //             'id_contrato'       => $idContrato,
    //             'id_conductor'      => $idConductor,
    //             'tipo'              => 'licencia',

    //             'numero_identificacion' => $data['numero_licencia'] ?? null,
    //             'pais_emision'      => $data['emite_licencia'] ?? null, // CORREGIDO
    //             'fecha_emision'     => $data['fecha_emision_licencia'] ?? null,
    //             'fecha_vencimiento' => $data['fecha_vencimiento_licencia'] ?? null,

    //             'id_archivo_frente' => $idLicFrente,
    //             'id_archivo_reverso' => $idLicReverso,

    //             'created_at'        => now(),
    //             'updated_at'        => now(),
    //         ]);


    //         // ============================
    //         // 6. VALIDAR LICENCIA VENCIDA
    //         // ============================
    //         if (!empty($data['fecha_vencimiento_licencia'])) {

    //             $vence = Carbon::parse($data['fecha_vencimiento_licencia']);

    //             if ($vence->isPast()) {

    //                 DB::table('contrato_evento')->insert([
    //                     'id_contrato' => $idContrato,
    //                     'evento'      => 'Licencia vencida detectada',
    //                     'detalle'     => json_encode([
    //                         'conductor' => $idConductor ? "Adicional #$idConductor" : 'Titular',
    //                         'vence'     => $vence->format('Y-m-d')
    //                     ]),
    //                     'realizado_en' => now(),
    //                     'created_at'  => now(),
    //                     'updated_at'  => now(),
    //                 ]);

    //                 return response()->json([
    //                     'warning' => true,
    //                     'msg' => '⚠️ La licencia está vencida. Por favor, sube una vigente.'
    //                 ]);
    //             }
    //         }


    //         // ============================
    //         // 7. RESPUESTA FINAL
    //         // ============================
    //         return response()->json([
    //             'success' => true,
    //             'msg'     => 'Documentación guardada correctamente.'
    //         ]);
    //     } catch (ValidationException $e) {

    //         return response()->json([
    //             'error' => 'Error de validación',
    //             'detalles' => $e->errors(),
    //             'files' => collect($request->files)->map(fn($f) => [
    //                 'nombre' => $f->getClientOriginalName(),
    //                 'mime'   => $f->getMimeType(),
    //                 'tamano' => $f->getSize(),
    //             ]),
    //         ], 422);
    //     } catch (\Throwable $e) {

    //         Log::error("ERROR guardarDocumentacion: " . $e->getMessage());

    //         return response()->json([
    //             'error'   => 'Error interno al guardar documentación.',
    //             'detalle' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * 📄 Guarda documentación masiva (Titular + Adicionales)
     */
    public function guardarDocumentacion(Request $request)
    {
        try {
            $idContrato    = $request->id_contrato;
            $idReservacion = $request->id_reservacion;
            $conductoresData = $request->input('conductores');

            if (!$conductoresData || !is_array($conductoresData)) {
                return response()->json(['error' => 'No se recibieron datos de conductores.'], 422);
            }

            // ====================================================
            // 🔧 FUNCIÓN INTERNA PARA GUARDAR IMAGEN EN TABLA 'archivos'
            // ====================================================
            $guardarImagen = function ($file) {
                if (!$file) return null;
                return DB::table('archivos')->insertGetId([
                    'nombre_original' => $file->getClientOriginalName(),
                    'tipo'            => 'imagen',
                    'contenido'       => file_get_contents($file->getRealPath()), // LONGBLOB
                    'extension'       => $file->extension(),
                    'mime_type'       => $file->getMimeType(),
                    'tamano_bytes'    => $file->getSize(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            };

            foreach ($conductoresData as $idx => $c) {
                // 1. Extraer archivos usando la nomenclatura de puntos de Laravel para arrays
                $idArchivoFrente  = $guardarImagen($request->file("conductores.$idx.idFrente"));
                $idArchivoReverso = $guardarImagen($request->file("conductores.$idx.idReverso"));
                $idLicFrente      = $guardarImagen($request->file("conductores.$idx.licFrente"));
                $idLicReverso     = $guardarImagen($request->file("conductores.$idx.licReverso"));

                $idConductor = $c['id_conductor'] ?? null;
                $esTitular   = (isset($c['es_titular']) && $c['es_titular'] == "1");

                // 2. LÓGICA SEGÚN TIPO DE CONDUCTOR
                if ($esTitular) {
                    // --- ACTUALIZAR DATOS EN LA RESERVACIÓN (Titular) ---
                    DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                        'nombre_cliente'    => $c['nombre'],
                        'apellidos_cliente' => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
                        'fecha_nacimiento'  => $c['fecha_nacimiento'] ?? null,
                        'updated_at'        => now()
                    ]);
                } else {
                    // --- CREAR O ACTUALIZAR CONDUCTOR ADICIONAL ---
                    if (empty($idConductor)) {
                        $idConductor = DB::table('contrato_conductor_adicional')->insertGetId([
                            'id_contrato'      => $idContrato,
                            'nombres'          => $c['nombre'],
                            'apellidos'        => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
                            'numero_licencia'  => $c['numero_licencia'] ?? null,
                            'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
                            'contacto'         => $c['contacto_emergencia'] ?? null,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    } else {
                        DB::table('contrato_conductor_adicional')->where('id_conductor', $idConductor)->update([
                            'nombres'          => $c['nombre'],
                            'apellidos'        => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
                            'numero_licencia'  => $c['numero_licencia'] ?? null,
                            'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
                            'contacto'         => $c['contacto_emergencia'] ?? null,
                            'updated_at'       => now()
                        ]);
                    }
                }

                // 3. GUARDAR DOCUMENTO: IDENTIFICACIÓN
                DB::table('contrato_documento')->updateOrInsert(
                    ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'identificacion'],
                    [
                        'tipo_identificacion'   => $c['tipo_identificacion'] ?? 'INE',
                        'numero_identificacion' => $c['numero_identificacion'] ?? null,
                        'nombre'                => $c['nombre'],
                        'apellido_paterno'      => $c['apellido_paterno'],
                        'apellido_materno'      => $c['apellido_materno'] ?? null,
                        'fecha_nacimiento'      => $c['fecha_nacimiento'] ?? null,
                        'fecha_vencimiento'     => $c['fecha_vencimiento_id'] ?? null,
                        // Mantenemos la foto anterior si no se subió una nueva
                        'id_archivo_frente'     => $idArchivoFrente ?? DB::raw('id_archivo_frente'),
                        'id_archivo_reverso'    => $idArchivoReverso ?? DB::raw('id_archivo_reverso'),
                        'updated_at'            => now(),
                    ]
                );

                // 4. GUARDAR DOCUMENTO: LICENCIA
                DB::table('contrato_documento')->updateOrInsert(
                    ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'licencia'],
                    [
                        'numero_identificacion' => $c['numero_licencia'] ?? null,
                        'pais_emision'          => $c['emite_licencia'] ?? 'México',
                        'fecha_emision'         => $c['fecha_emision_licencia'] ?? null,
                        'fecha_vencimiento'     => $c['fecha_vencimiento_licencia'] ?? null,
                        // Mantenemos la foto anterior si no se subió una nueva
                        'id_archivo_frente'     => $idLicFrente ?? DB::raw('id_archivo_frente'),
                        'id_archivo_reverso'    => $idLicReverso ?? DB::raw('id_archivo_reverso'),
                        'updated_at'            => now(),
                    ]
                );
            }

            return response()->json(['success' => true, 'msg' => 'Toda la documentación se guardó correctamente.']);
        } catch (\Exception $e) {
            Log::error("ERROR guardarDocumentacion Masiva: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al guardar.', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function obtenerDocumentacion($idContrato)
    {
        try {

            // ====================================================
            // 🔧 Función interna para crear URL de archivo
            // ====================================================
            $makeUrl = function ($idArchivo) {
                return $idArchivo ? route('archivo.mostrar', ['id' => $idArchivo]) : null;
            };

            // ====================================================
            // 🔹 OBTENER DOCUMENTOS DEL TITULAR
            // ====================================================
            $docsTitular = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->whereNull('id_conductor')
                ->get();

            $ident = $docsTitular->firstWhere('tipo', 'identificacion');
            $lic   = $docsTitular->firstWhere('tipo', 'licencia');

            // ====================================================
            // 🔹 Determinar si la licencia está vencida
            // ====================================================
            $licenciaVencida = false;
            if ($lic && $lic->fecha_vencimiento) {
                $licenciaVencida = \Carbon\Carbon::parse($lic->fecha_vencimiento)->isPast();
            }

            // ====================================================
            // 🔹 UNIFICAR CAMPOS DEL TITULAR
            // ====================================================
            $titularCampos = [
                // Identificación
                'tipo_identificacion'   => $ident->tipo_identificacion ?? null,
                'numero_identificacion' => $ident->numero_identificacion ?? null,
                'nombre'                => $ident->nombre ?? null,
                'apellido_paterno'      => $ident->apellido_paterno ?? null,
                'apellido_materno'      => $ident->apellido_materno ?? null,
                'fecha_nacimiento'      => $ident->fecha_nacimiento ?? null,
                'fecha_vencimiento_id'  => $ident->fecha_vencimiento ?? null,

                // Licencia
                'numero_licencia'         => $lic->numero_identificacion ?? null,
                'pais_emision'            => $lic->pais_emision ?? null,
                'fecha_emision_licencia'  => $lic->fecha_emision ?? null,
                'fecha_vencimiento_licencia' => $lic->fecha_vencimiento ?? null,

                // Contacto
                'contacto_emergencia' => null // No estaba en esta tabla
            ];

            // ====================================================
            // 🔹 UNIFICAR ARCHIVOS DE TITULAR
            // ====================================================
            $titularArchivos = [
                'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
                'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
                'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
                'licReverso_url' => $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
            ];

            // ====================================================
            // 🔹 TITULAR FINAL, FORMATO EXACTO PARA JS
            // ====================================================
            $titular = [
                'campos'           => $titularCampos,
                'archivos'         => $titularArchivos,
                'licencia_vencida' => $licenciaVencida,
            ];

            // ====================================================
            // 🔹 OBTENER CONDUCTORES ADICIONALES
            // ====================================================
            $conductores = DB::table('contrato_conductor_adicional')
                ->where('id_contrato', $idContrato)
                ->get();

            $docsAdicionales = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->whereNotNull('id_conductor')
                ->get()
                ->groupBy('id_conductor');

            $adicionalesData = [];

            foreach ($conductores as $c) {

                $grupo = $docsAdicionales->get($c->id_conductor, collect());
                $ident = $grupo->firstWhere('tipo', 'identificacion');
                $lic   = $grupo->firstWhere('tipo', 'licencia');

                $vencida = false;
                if ($lic && $lic->fecha_vencimiento) {
                    $vencida = \Carbon\Carbon::parse($lic->fecha_vencimiento)->isPast();
                }

                // 📌 Unificar campos del adicional
                $campos = [
                    'tipo_identificacion'   => $ident->tipo_identificacion ?? null,
                    'numero_identificacion' => $ident->numero_identificacion ?? null,
                    'nombre'                => $ident->nombre ?? $c->nombres,
                    'apellido_paterno'      => $ident->apellido_paterno ?? null,
                    'apellido_materno'      => $ident->apellido_materno ?? null,
                    'fecha_nacimiento'      => $ident->fecha_nacimiento ?? $c->fecha_nacimiento,
                    'fecha_vencimiento_id'  => $ident->fecha_vencimiento ?? null,

                    'numero_licencia'         => $lic->numero_identificacion ?? $c->numero_licencia,
                    'pais_emision'            => $lic->pais_emision ?? $c->pais_licencia,
                    'fecha_emision_licencia'  => $lic->fecha_emision ?? null,
                    'fecha_vencimiento_licencia' => $lic->fecha_vencimiento ?? null,

                    'contacto_emergencia'     => $c->contacto,
                ];

                // 📌 Unificar archivos del adicional
                $archivos = [
                    'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
                    'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
                    'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
                    'licReverso_url' => $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
                ];

                // 📌 Estructura EXACTA para JS
                $adicionalesData[$c->id_conductor] = [
                    'campos'           => $campos,
                    'archivos'         => $archivos,
                    'licencia_vencida' => $vencida,
                ];
            }

            // ====================================================
            // 🎯 RESPUESTA EXACTA PARA EL JS
            // ====================================================
            return response()->json([
                'success'    => true,
                'documentos' => [
                    'titular'     => $titular,
                    'adicionales' => $adicionalesData,
                ],
            ]);
        } catch (\Throwable $e) {

            Log::error("ERROR obtenerDocumentacion: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg'     => 'Error interno al obtener documentación'
            ], 500);
        }
    }

    public function verificarDocumentosExistentes($idContrato)
    {
        try {

            $existen = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->exists();

            return response()->json([
                'success' => true,
                'existen' => $existen
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'existen' => false,
                'msg' => 'Error verificando documentos'
            ], 500);
        }
    }

    public function obtenerConductores($idContrato)
    {
        try {
            $conductores = DB::table('contrato_conductor_adicional')
                ->where('id_contrato', $idContrato)
                ->select('id_conductor', 'nombres', 'apellidos')
                ->orderBy('id_conductor')
                ->get();

            return response()->json($conductores);
        } catch (\Throwable $e) {
            Log::error("Error en ContratoController@obtenerConductores: " . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los conductores adicionales.'], 500);
        }
    }

    public function resumenPaso6($idReservacion)
    {
        try {
            // ===============================
            // 1) Reservación
            // ===============================
            $res = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$res) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Reservación no encontrada'
                ]);
            }

            // ===============================
            // 2) Calcular días (correcto con +1)
            // ===============================
            $dias = Carbon::parse($res->fecha_inicio)
                ->diffInDays(Carbon::parse($res->fecha_fin));

            // ===============================
            // 3) Tarifa correcta
            //    PRIORIDAD: tarifa_modificada → tarifa_base
            // ===============================
            $tarifa = $res->tarifa_modificada !== null
                ? $res->tarifa_modificada
                : $res->tarifa_base;

            $baseTotal = $tarifa * $dias;

            // ===============================
            // 4) Servicios adicionales
            // ===============================
            $adds = DB::table('reservacion_servicio')
                ->where('id_reservacion', $idReservacion)
                ->selectRaw("SUM(cantidad * precio_unitario * $dias) as total")
                ->first()->total ?? 0;

            // ===============================
            // 5) Delivery
            // ===============================
            $delivery = $res->delivery_total ?? 0;

            // ===============================
            // 6) Seguros
            // ===============================
            $precioSeguros = $this->calcularTotalProtecciones($idReservacion);

            // ===============================
            // 7) Cargos adicionales del contrato
            // ===============================
            $contrato = DB::table('contratos')
                ->where('id_reservacion', $idReservacion)
                ->first();

            $cargos = 0;

            if ($contrato) {
                $cargos = DB::table('cargo_adicional')
                    ->where('id_contrato', $contrato->id_contrato)
                    ->sum('monto');
            }

            // ===============================
            // 8) Subtotal, IVA, total
            // ===============================
            $subtotal = $baseTotal + $adds + $delivery + $precioSeguros + $cargos;
            $iva = $subtotal * 0.16;
            $totalContrato = $subtotal + $iva;

            // ===============================
            // 9.1 Guardar totales reales en DB
            // ===============================
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'subtotal' => $subtotal,
                    'impuestos' => $iva,
                    'total' => $totalContrato,
                    'updated_at' => now(),
                ]);

            // ===============================
            // 9) Pagos
            // ===============================
            $pagos = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->orderBy('created_at')
                ->get()
                ->map(function ($p) {
                    return [
                        'id_pago' => $p->id_pago,
                        'fecha'   => Carbon::parse($p->created_at)->format('Y-m-d H:i'),
                        'tipo'    => $p->tipo_pago,
                        'origen'  => $p->origen_pago ?? $p->metodo,
                        'monto'   => $p->monto,
                    ];
                });

            $totalPagado = $pagos->sum('monto');
            $saldoPendiente = $totalContrato - $totalPagado;

            // ===============================
            // 🔥 10) Respuesta final (JSON)
            // Incluye tarifas para que JS pueda actualizar correctamente
            // ===============================
            return response()->json([
                'ok' => true,
                'data' => [
                    'base' => [
                        'dias' => $dias,
                        'tarifa' => $tarifa,
                        'tarifa_base' => $res->tarifa_base,
                        'tarifa_modificada' => $res->tarifa_modificada,
                        'descripcion' => "{$dias} días · {$tarifa} por día",
                        'total' => $baseTotal
                    ],
                    'adicionales' => [
                        'servicios' => $adds,
                        'delivery' => $delivery,
                        'seguros' => $precioSeguros,
                        'cargos' => $cargos,
                        'total' => $adds + $delivery + $precioSeguros + $cargos
                    ],
                    'totales' => [
                        'subtotal'        => $subtotal,
                        'iva'             => $iva,
                        'total_contrato'  => $totalContrato,
                        'saldo_pendiente' => $saldoPendiente,
                    ],
                    'pagos' => $pagos,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("Error resumenPaso6: " . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error interno'], 500);
        }
    }

    public function agregarPagoPaso6(Request $request)
    {
        try {
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'tipo_pago'      => 'required|string|max:50',
                'monto'          => 'required|numeric|min:0.01',
                'ultimos4'       => 'nullable|string|max:10',
                'auth'           => 'nullable|string|max:50',
                'notas'          => 'nullable|string|max:500',
                'metodo'         => 'nullable|string|max:50',
            ]);

            // 🔹 Normalizar método
            $metodo = strtoupper($data['metodo'] ?? 'EFECTIVO');

            // 🔹 Definir ORIGEN según método (igual que en pagoManual)
            $origen = match ($metodo) {
                'VISA', 'MASTERCARD', 'AMEX', 'DEBITO' => 'terminal',
                'TRANSFERENCIA', 'SPEI', 'DEPOSITO'    => 'mostrador',
                'EFECTIVO'                             => 'mostrador',
                default                                => 'mostrador',
            };

            DB::table('pagos')->insert([
                'id_reservacion' => $data['id_reservacion'],
                'id_contrato'    => null,

                'origen_pago'    => $origen,      // ✅ ahora sí se llena
                'metodo'         => $metodo,
                'tipo_pago'      => $data['tipo_pago'],

                'monto'          => $data['monto'],
                'moneda'         => 'MXN',
                'estatus'        => 'paid',

                'payload_webhook' => json_encode([
                    'ultimos4' => $data['ultimos4'] ?? null,
                    'auth'     => $data['auth'] ?? null,
                    'notas'    => $data['notas'] ?? null,
                ]),

                'captured_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error("Error agregarPagoPaso6: " . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error interno'], 500);
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

    public function eliminarPago($idPago)
    {
        try {
            // Buscamos y borramos el pago
            $eliminado = DB::table('pagos')->where('id_pago', $idPago)->delete();

            if ($eliminado) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'msg' => 'El pago no existe o ya fue eliminado'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Error en BD: ' . $e->getMessage()], 500);
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
            // 1) Identificamos el origen exacto que quieres mostrar
            $origenFront = strtolower($req->input('origen', ''));

            if (str_contains($origenFront, 'linea') || str_contains($origenFront, 'paypal')) {
                $origen = 'PayPal';
            } elseif (str_contains($origenFront, 'terminal')) {
                $origen = 'Terminal';
            } elseif (str_contains($origenFront, 'transferencia') || str_contains($origenFront, 'deposito')) {
                $origen = 'Transferencia / Depósito';
            } else {
                $origen = 'Efectivo';
            }

            // 2) Subir comprobante
            $filePath = null;
            if ($req->hasFile('comprobante')) {
                $filePath = $req->file('comprobante')->store('pagos', 'public');
            }

            // 3) Insertar el pago
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion' => $req->id_reservacion,
                'id_contrato'    => null,
                'origen_pago'    => $origen, // Guardará el nombre bonito
                'metodo'         => strtoupper($req->metodo),
                'tipo_pago'      => strtoupper($req->tipo_pago),
                'monto'          => $req->monto,
                'moneda'         => 'MXN',
                'estatus'        => 'paid',
                'comprobante'    => $filePath,
                'payload_webhook' => json_encode(['notas' => $req->notas]),
                'captured_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 4) Actualizar reservación si se completó el total
            $res = DB::table('reservaciones')->where('id_reservacion', $req->id_reservacion)->first();
            $pagado = DB::table('pagos')
                ->where('id_reservacion', $req->id_reservacion)
                ->where('estatus', 'paid')
                ->sum('monto');

            if (($res->total - $pagado) <= 0) {
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
            return response()->json(['ok' => true, 'id_pago' => $idPago]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error pagoManual: " . $th->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error backend: ' . $th->getMessage()]);
        }
    }

    public function finalizar($idReservacion)
    {
        try {
            // 1) Validar reservación
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$reservacion) {
                return redirect()->back()->with('error', 'Reservación no encontrada.');
            }

            // 2) Verificar si ya existe contrato
            $contratoExistente = DB::table('contratos')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if ($contratoExistente) {
                return redirect()->route('contrato.final', $contratoExistente->id_contrato);
            }

            // 3) Generar número único de contrato
            $numeroContrato = 'CTR-' . strtoupper(bin2hex(random_bytes(4)));

            // 4) Crear contrato
            $idContrato = DB::table('contratos')->insertGetId([
                'id_reservacion'  => $idReservacion,
                'id_asesor'       => session('id_usuario') ?? null,
                'numero_contrato' => $numeroContrato,
                'estado'          => 'abierto',
                'abierto_en'      => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // 5) Redirigir al contrato final
            return redirect()->route('contrato.final', $idContrato);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al finalizar contrato: ' . $e->getMessage());
        }
    }

    public function editarTarifa(Request $request, $idReservacion)
    {
        try {
            $nuevoValor = $request->tarifa_modificada;

            if (!$nuevoValor || $nuevoValor <= 0) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Tarifa inválida'
                ]);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'tarifa_modificada' => $nuevoValor
                ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Error al actualizar tarifa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editarCortesia(Request $request, $idReservacion)
    {
        try {
            $horas = (int) $request->cortesias;

            if (!in_array($horas, [1, 2, 3])) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Las horas de cortesía deben ser 1, 2 o 3'
                ]);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'horas_cortesia' => $horas
                ]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Error al guardar horas de cortesía',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function obtenerClienteContrato($idContrato)
    {
        $contrato = DB::table('contratos')
            ->join('reservaciones', 'reservaciones.id_reservacion', '=', 'contratos.id_reservacion')
            ->where('contratos.id_contrato', $idContrato)
            ->first([
                'reservaciones.nombre_cliente',
                'reservaciones.apellidos_cliente'
            ]);

        if (!$contrato) {
            return response()->json(null);
        }

        return response()->json($contrato);
    }

    public function status($idReservacion)
    {
        $existe = DB::table('contratos')
            ->where('id_reservacion', $idReservacion)
            ->exists();

        return response()->json(['existe' => $existe]);
    }
}

// public function registrar Pago(Request $request)
//     {
//         try {
//             $data = $request->validate([
//                 'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
//                 'id_contrato'    => 'required|integer|exists:contratos,id_contrato',
//                 'metodo'         => 'required|string|max:50',
//                 'tipo_pago'      => 'required|string|max:50',
//                 'monto'          => 'required|numeric|min:0.01',
//                 'notas'          => 'nullable|string',
//                 'extra_datos'    => 'nullable|array'
//             ]);p

//             // Insertar pago
//             DB::table('pagos')->insert([
//                 'id_reservacion'      => $data['id_reservacion'],
//                 'id_contrato'         => $data['id_contrato'],
//                 'metodo'              => $data['metodo'],
//                 'tipo_pago'           => $data['tipo_pago'],
//                 'monto'               => $data['monto'],
//                 'estatus'             => 'paid',
//                 'moneda'              => 'MXN',
//                 'payload_webhook'     => json_encode($data['extra_datos'] ?? null),
//                 'created_at'          => now(),
//                 'updated_at'          => now(),
//             ]);

//             return response()->json([
//                 'success' => true,
//                 'msg'     => 'Pago registrado correctamente'
//             ]);
//         } catch (\Throwable $e) {
//             Log::error("ERROR registrarPago: " . $e->getMessage());
//             return response()->json(['error' => 'Error interno al registrar pago'], 500);
//         }
//     }

//     public function eliminarPago($idPago)
//     {
//         try {
//             DB::table('pagos')->where('id_pago', $idPago)->delete();
//             return response()->json(['ok' => true]);
//         } catch (\Throwable $e) {
//             Log::error("Error eliminarPago: " . $e->getMessage());
//             return response()->json(['ok' => false], 500);
//         }
//     }
//     public function pagoPayPal(Request $req)
//     {
//         $req->validate([
//             'id_reservacion' => 'required|integer',
//             'order_id'       => 'required|string',
//             'monto'          => 'required|numeric|min:1',
//         ]);

//         DB::beginTransaction();

//         try {
//             $res = DB::table('reservaciones')
//                 ->where('id_reservacion', $req->id_reservacion)
//                 ->first();

//             if (!$res) {
//                 return response()->json(['ok' => false, 'msg' => 'Reservación no encontrada'], 404);
//             }

//             // Crear el registro de pago
//             $idPago = DB::table('pagos')->insertGetId([
//                 'id_reservacion'       => $req->id_reservacion,
//                 'id_contrato'          => null,

//                 'origen_pago'          => 'online',
//                 'pasarela'             => 'paypal',
//                 'referencia_pasarela'  => $req->order_id,

//                 'estatus'              => 'paid',
//                 'metodo'               => 'PayPal',
//                 'tipo_pago'            => 'PAGO RESERVACIÓN',

//                 'monto'                => $req->monto,
//                 'moneda'               => 'MXN',

//                 'payload_webhook'      => null,
//                 'captured_at'          => now(),

//                 'created_at'           => now(),
//                 'updated_at'           => now(),
//             ]);

//             // Actualizar reservación
//             DB::table('reservaciones')
//                 ->where('id_reservacion', $req->id_reservacion)
//                 ->update([
//                     'paypal_order_id' => $req->order_id,
//                     'status_pago'     => 'Pagado',
//                     'metodo_pago'     => 'en línea',
//                     'estado'          => 'confirmada',
//                     'updated_at'      => now(),
//                 ]);

//             DB::commit();

//             return response()->json([
//                 'ok' => true,
//                 'msg' => 'Pago registrado',
//                 'id_pago' => $idPago
//             ]);
//         } catch (\Throwable $th) {
//             DB::rollBack();
//             return response()->json(['ok' => false, 'msg' => $th->getMessage()]);
//         }
//     }

//     public function pagoManual(Request $req)
//     {
//         $req->validate([
//             'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
//             'tipo_pago'      => 'required|string|max:50',
//             'metodo'         => 'required|string|max:50',
//             'monto'          => 'required|numeric|min:1',
//             'notas'          => 'nullable|string|max:500',
//             'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf',
//         ]);

//         DB::beginTransaction();

//         try {
//             // ---------------------------------------------------
//             // 1) Determinar ORIGEN DEL PAGO según el método
//             // ---------------------------------------------------
//             $origen = match (strtoupper($req->metodo)) {
//                 'EFECTIVO'         => 'mostrador',
//                 'TRANSFERENCIA',
//                 'SPEI',
//                 'DEPOSITO'         => 'mostrador',
//                 'VISA',
//                 'MASTERCARD',
//                 'AMEX',
//                 'DEBITO'           => 'terminal',
//                 default            => 'mostrador',
//             };

//             // ---------------------------------------------------
//             // 2) Subir comprobante SI existe
//             // ---------------------------------------------------
//             $filePath = null;

//             if ($req->hasFile('comprobante')) {
//                 $filePath = $req->file('comprobante')->store('pagos', 'public');
//             }

//             // ---------------------------------------------------
//             // 3) Insertar el pago manual
//             // ---------------------------------------------------
//             $idPago = DB::table('pagos')->insertGetId([
//                 'id_reservacion' => $req->id_reservacion,
//                 'id_contrato'    => null,

//                 'origen_pago' => $origen,
//                 'metodo'      => strtoupper($req->metodo),
//                 'tipo_pago'   => strtoupper($req->tipo_pago),

//                 'monto'       => $req->monto,
//                 'moneda'      => 'MXN',
//                 'estatus'     => 'paid',

//                 'comprobante' => $filePath,
//                 'pasarela'    => null,
//                 'referencia_pasarela' => null,

//                 'payload_webhook' => json_encode([
//                     'notas' => $req->notas,
//                 ]),

//                 'captured_at' => now(),
//                 'created_at'  => now(),
//                 'updated_at'  => now(),
//             ]);

//             // ---------------------------------------------------
//             // 4) Verificar si queda saldo pendiente y actualizar reservación
//             // ---------------------------------------------------
//             $res = DB::table('reservaciones')->where('id_reservacion', $req->id_reservacion)->first();

//             $pagado = DB::table('pagos')
//                 ->where('id_reservacion', $req->id_reservacion)
//                 ->where('estatus', 'paid')
//                 ->sum('monto');

//             $saldo = $res->total - $pagado;

//             if ($saldo <= 0) {
//                 DB::table('reservaciones')
//                     ->where('id_reservacion', $req->id_reservacion)
//                     ->update([
//                         'status_pago' => 'Pagado',
//                         'metodo_pago' => $origen,
//                         'estado'      => 'confirmada',
//                         'updated_at'  => now(),
//                     ]);
//             }

//             DB::commit();

//             return response()->json([
//                 'ok' => true,
//                 'id_pago' => $idPago,
//             ]);
//         } catch (\Throwable $th) {
//             DB::rollBack();
//             Log::error("Error pagoManual: " . $th->getMessage());

//             return response()->json([
//                 'ok' => false,
//                 'msg' => 'Error interno al registrar el pago',
//             ], 500);
//         }
//     }


//     public function pagoEfectivo(Request $req)
//     {
//         $req->validate([
//             'id_reservacion' => 'required|integer',
//             'tipo_pago'      => 'required|string',
//             'monto'          => 'required|numeric|min:1',
//             'notas'          => 'nullable|string|max:500',
//         ]);

//         $idPago = DB::table('pagos')->insertGetId([
//             'id_reservacion' => $req->id_reservacion,

//             'origen_pago' => 'mostrador',
//             'metodo'      => 'EFECTIVO',
//             'tipo_pago'   => $req->tipo_pago,
//             'monto'       => $req->monto,
//             'moneda'      => 'MXN',

//             'estatus'     => 'paid',
//             'pasarela'    => null,
//             'referencia_pasarela' => null,

//             'payload_webhook' => json_encode([
//                 'notas' => $req->notas,
//             ]),

//             'captured_at' => now(),
//             'created_at'  => now(),
//             'updated_at'  => now(),
//         ]);

//         return response()->json(['ok' => true, 'id_pago' => $idPago]);
//     }

//     public function pagoTerminal(Request $req)
//     {
//         $req->validate([
//             'id_reservacion' => 'required|integer',
//             'tipo_pago'      => 'required|string',
//             'metodo'         => 'required|string', // VISA, MASTERCARD, AMEX, DEBITO
//             'monto'          => 'required|numeric|min:1',
//             'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
//         ]);

//         // Guardar ticket
//         $filePath = $req->file('comprobante')->store('pagos', 'public');

//         $idPago = DB::table('pagos')->insertGetId([
//             'id_reservacion' => $req->id_reservacion,

//             'origen_pago' => 'terminal',
//             'metodo'      => $req->metodo,
//             'tipo_pago'   => $req->tipo_pago,

//             'monto'       => $req->monto,
//             'moneda'      => 'MXN',

//             'estatus'     => 'paid',
//             'comprobante' => $filePath,

//             'pasarela'    => null,
//             'referencia_pasarela' => null,

//             'payload_webhook' => null,
//             'captured_at'     => now(),

//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         return response()->json(['ok' => true, 'id_pago' => $idPago]);
//     }
//     public function pagoTransferencia(Request $req)
//     {
//         $req->validate([
//             'id_reservacion' => 'required|integer',
//             'tipo_pago'      => 'required|string',
//             'metodo'         => 'required|string', // TRANSFERENCIA / SPEI / DEPOSITO
//             'monto'          => 'required|numeric|min:1',
//             'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
//             'notas'          => 'nullable|string|max:500',
//         ]);

//         $filePath = $req->file('comprobante')->store('pagos', 'public');

//         $idPago = DB::table('pagos')->insertGetId([
//             'id_reservacion' => $req->id_reservacion,

//             'origen_pago' => 'mostrador',
//             'metodo'      => $req->metodo,
//             'tipo_pago'   => $req->tipo_pago,

//             'monto'       => $req->monto,
//             'moneda'      => 'MXN',

//             'estatus'     => 'paid',
//             'comprobante' => $filePath,

//             'pasarela'    => null,
//             'referencia_pasarela' => null,

//             'payload_webhook' => json_encode([
//                 'notas' => $req->notas,
//             ]),

//             'captured_at' => now(),

//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         return response()->json(['ok' => true, 'id_pago' => $idPago]);
//     }