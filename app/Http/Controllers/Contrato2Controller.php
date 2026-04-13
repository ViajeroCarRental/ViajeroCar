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
    private function pagosReservacionQuery($idReservacion)
    {
        return DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->where(function ($query) {
                $query->whereNull('tipo_pago')
                    ->orWhereRaw('UPPER(TRIM(tipo_pago)) <> ?', ['GARANTIA']);
            });
    }

    private function pagosGarantiaQuery($idReservacion)
    {
        return DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->whereRaw('UPPER(TRIM(COALESCE(tipo_pago, ""))) = ?', ['GARANTIA']);
    }

    private function tablaGarantiasSeguro(): array
    {
        return [
            'C' => ['ldw' => 5000, 'pdw' => 8000, 'cdw10' => 15000, 'cdw20' => 25000, 'declined' => 330000],
            'D' => ['ldw' => 5000, 'pdw' => 8000, 'cdw10' => 18000, 'cdw20' => 25000, 'declined' => 380000],
            'E' => ['ldw' => 5000, 'pdw' => 8000, 'cdw10' => 20000, 'cdw20' => 30000, 'declined' => 500000],
            'F' => ['ldw' => 5000, 'pdw' => 15000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 650000],
            'IC' => ['ldw' => 5000, 'pdw' => 8000, 'cdw10' => 20000, 'cdw20' => 30000, 'declined' => 500000],
            'I' => ['ldw' => 5000, 'pdw' => 10000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 600000],
            'IB' => ['ldw' => 5000, 'pdw' => 8000, 'cdw10' => 18000, 'cdw20' => 25000, 'declined' => 400000],
            'M' => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 800000],
            'L' => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 800000],
            'H' => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 600000],
            'HI' => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000, 'declined' => 900000],
        ];
    }

    private function claveGarantiaPorSeguro(?string $nombreSeguro): string
    {
        $nombre = strtolower(trim($nombreSeguro ?? ''));

        if ($nombre === '') {
            return 'declined';
        }

        if (str_contains($nombre, 'pdw')) {
            return 'pdw';
        }

        if (str_contains($nombre, 'ldw')) {
            return 'ldw';
        }

        if (str_contains($nombre, '10')) {
            return 'cdw10';
        }

        if (str_contains($nombre, '20')) {
            return 'cdw20';
        }

        if (str_contains($nombre, 'declin')) {
            return 'declined';
        }

        return 'declined';
    }

    private function obtenerGarantiaSeguro(?string $codigoCategoria, ?string $nombreSeguro): array
    {
        $codigo = strtoupper($codigoCategoria ?: 'C');
        $clave = $this->claveGarantiaPorSeguro($nombreSeguro);
        $tabla = $this->tablaGarantiasSeguro();
        $monto = $tabla[$codigo][$clave] ?? 0;

        return [
            'codigo_categoria' => $codigo,
            'tipo_seguro' => $clave,
            'nombre_seguro' => $nombreSeguro ?: 'Sin paquete',
            'monto' => $monto,
        ];
    }

    private function formatearTelefonoContrato(?string $telefono): string
    {
        $soloNumeros = preg_replace('/[^0-9]/', '', $telefono ?? '');

        if (strlen($soloNumeros) === 10) {
            return '(' . substr($soloNumeros, 0, 3) . ') ' . substr($soloNumeros, 3, 3) . '-' . substr($soloNumeros, 6);
        }

        return $telefono ?: '--';
    }

    public function mostrarContrato2($id)
    {
        try {
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('contratos as c', 'r.id_reservacion', '=', 'c.id_reservacion')
                ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
                ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria', '=', 'cck.id_categoria')
                ->where(function ($query) use ($id) {
                    $query->where('r.id_reservacion', $id)
                        ->orWhere('c.id_contrato', $id);
                })
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
                $idContrato = $this->crearContratoRapido($idReservacion);
                $reservacion->id_contrato = $idContrato;
                $reservacion->numero_contrato = str_pad($idContrato, 4, '0', STR_PAD_LEFT);
            }

            $ubicaciones = Cache::remember('ubicaciones_servicio', 86400, function () {
                return DB::table('ubicaciones_servicio')
                    ->select('id_ubicacion', 'estado', 'destino', 'km')
                    ->where('activo', 1)
                    ->get();
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

            $conductoresExtras = collect();
            $idServicioConductor = 4;

            if (isset($serviciosReservados[$idServicioConductor])) {
                $cantidad = $serviciosReservados[$idServicioConductor];

                // UNA SOLA QUERY para todos los conductores
                $conductoresDb = DB::table('contrato_conductor_adicional')
                    ->where('id_contrato', $idContrato)
                    ->limit($cantidad)
                    ->get();

                for ($i = 1; $i <= $cantidad; $i++) {
                    $conductorDb = $conductoresDb[$i - 1] ?? null;

                    $conductoresExtras->push([
                        'id_conductor' => $conductorDb->id_conductor ?? null,
                        'nombres' => $conductorDb->nombres ?? "Conductor adicional $i"
                    ]);
                }
            }

            $idServicioMenor = Cache::remember('id_servicio_menor', 86400, function () {
                return DB::table('servicios')
                    ->where('nombre', 'LIKE', '%menor%')
                    ->value('id_servicio');
            });

            $catActual = $categorias->firstWhere('id_categoria', $reservacion->id_categoria);
            $fechaInicio = Carbon::parse($reservacion->fecha_inicio ?? now());
            $fechaFin = Carbon::parse($reservacion->fecha_fin ?? now()->addDay());
            $horaRetiro = Carbon::parse($reservacion->hora_retiro ?? '12:00:00');
            $horaEntrega = Carbon::parse($reservacion->hora_entrega ?? '12:00:00');
            $diasTotales = max(1, $fechaInicio->diffInDays($fechaFin));

            $precioBase = $catActual->precio_dia ?? ($catActual->precio ?? 0);
            $precioReal = ($reservacion->tarifa_ajustada == 1 && $reservacion->tarifa_modificada > 0)
                ? $reservacion->tarifa_modificada
                : $precioBase;

            $subtotal = $diasTotales * $precioReal;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;
            $telFinal = $this->formatearTelefonoContrato($reservacion->telefono_cliente ?? '');

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
                'fechaInicio'         => $fechaInicio,
                'fechaFin'            => $fechaFin,
                'horaRetiro'          => $horaRetiro,
                'horaEntrega'         => $horaEntrega,
                'diasTotales'         => $diasTotales,
                'precioReal'          => $precioReal,
                'subtotal'            => $subtotal,
                'iva'                 => $iva,
                'total'               => $total,
                'telFinal'            => $telFinal,
                'delivery' => (object)[
                    'activo'       => $reservacion->delivery_activo,
                    'id_ubicacion' => $reservacion->delivery_ubicacion,
                    'direccion'    => $reservacion->delivery_direccion,
                    'kms'          => $reservacion->delivery_km,
                    'total'        => $reservacion->delivery_total
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en mostrarContrato2: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el contrato.');
        }
    }

    private function crearContratoRapido($idReservacion)
    {
        return DB::transaction(function () use ($idReservacion) {
            $nuevoId = DB::table('contratos')->insertGetId([
                'id_reservacion'  => $idReservacion,
                'id_asesor'       => session('id_usuario') ?? null,
                'numero_contrato' => 'TEMP',
                'estado'          => 'abierto',
                'abierto_en'      => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $folioFormateado = str_pad($nuevoId, 4, '0', STR_PAD_LEFT);

            DB::table('contratos')
                ->where('id_contrato', $nuevoId)
                ->update(['numero_contrato' => $folioFormateado]);

            return $nuevoId;
        });
    }

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
            $idReservacion = $request->id_reservacion;
            $idConcepto = $request->id_concepto;

            if ($idConcepto == 5 || $idConcepto == 6) {
                return response()->json([
                    'success' => true,
                    'action'  => 'ignored',
                    'msg'     => 'Protegido por el escudo backend'
                ]);
            }

            $forzar = $request->forzar ?? null;

            if ((!$idContrato && !$idReservacion) || !$idConcepto) {
                return response()->json(['success' => false, 'msg' => 'Falta el ID del contrato/reservación o el concepto.']);
            }

            // Buscamos si ya existe el cargo (por contrato o por reservación)
            $query = DB::table('cargo_adicional')
                ->where('id_concepto', $idConcepto)
                ->where(function ($q) use ($idContrato, $idReservacion) {
                    if ($idContrato) $q->where('id_contrato', $idContrato);
                    if ($idReservacion) $q->orWhere('id_reservacion', $idReservacion);
                });

            $existe = $query->exists();

            if ($forzar === 'off') {
                if ($existe) {
                    $query->delete();
                }
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            $conceptoDb = DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->first();

            if ($forzar === 'on') {
                if (!$existe) {
                    DB::table('cargo_adicional')->insert([
                        'id_contrato'    => $idContrato,
                        'id_reservacion' => $idReservacion, // 🔥 Sincronizado
                        'id_concepto'    => $idConcepto,
                        'concepto'       => $conceptoDb->nombre ?? 'Cargo adicional',
                        'monto'          => $conceptoDb->monto_base ?? 0,
                        'created_at'     => now(),
                        'updated_at'     => now()
                    ]);
                }
                return response()->json(['success' => true, 'action' => 'inserted']);
            }

            if ($existe) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            } else {
                DB::table('cargo_adicional')->insert([
                    'id_contrato'    => $idContrato,
                    'id_reservacion' => $idReservacion, // 🔥 Sincronizado
                    'id_concepto'    => $idConcepto,
                    'concepto'       => $conceptoDb->nombre ?? 'Cargo adicional',
                    'monto'          => $conceptoDb->monto_base ?? 0,
                    'created_at'     => now(),
                    'updated_at'     => now()
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
    // public function guardarDocumentacion(Request $request)
    // {
    //     try {
    //         $idContrato    = $request->id_contrato;
    //         $idReservacion = $request->id_reservacion;
    //         $conductoresData = $request->input('conductores');

    //         if (!$conductoresData || !is_array($conductoresData)) {
    //             return response()->json(['error' => 'No se recibieron datos de conductores.'], 422);
    //         }

    //         // ====================================================
    //         // 🔥 ESCUDO BACKEND: VALIDAR CONTACTO DE EMERGENCIA
    //         // ====================================================
    //         foreach ($conductoresData as $c) {
    //             $esTitular = (isset($c['es_titular']) && $c['es_titular'] == "1");

    //             if (!$esTitular && !empty($c['contacto_emergencia'])) {
    //                 $contacto = trim($c['contacto_emergencia']);

    //                 // Exige estrictamente 10 números
    //                 if (!preg_match('/^\d{10}$/', $contacto)) {
    //                     $nombre = $c['nombre'] ?? 'un conductor adicional';
    //                     return response()->json([
    //                         'error' => "El teléfono de emergencia de {$nombre} debe tener exactamente 10 dígitos."
    //                     ], 422);
    //                 }
    //             }
    //         }

    //         // ====================================================
    //         // 🔧 FUNCIÓN INTERNA PARA GUARDAR IMAGEN EN TABLA 'archivos'
    //         // ====================================================
    //         $guardarImagen = function ($file) {
    //             if (!$file) return null;
    //             return DB::table('archivos')->insertGetId([
    //                 'nombre_original' => $file->getClientOriginalName(),
    //                 'tipo'            => 'imagen',
    //                 'contenido'       => file_get_contents($file->getRealPath()), // LONGBLOB
    //                 'extension'       => $file->extension(),
    //                 'mime_type'       => $file->getMimeType(),
    //                 'tamano_bytes'    => $file->getSize(),
    //                 'created_at'      => now(),
    //                 'updated_at'      => now(),
    //             ]);
    //         };

    //         foreach ($conductoresData as $idx => $c) {
    //             // 1. Extraer archivos usando la nomenclatura de puntos de Laravel para arrays
    //             $idArchivoFrente  = $guardarImagen($request->file("conductores.$idx.idFrente"));
    //             $idArchivoReverso = $guardarImagen($request->file("conductores.$idx.idReverso"));
    //             $idLicFrente      = $guardarImagen($request->file("conductores.$idx.licFrente"));
    //             $idLicReverso     = $guardarImagen($request->file("conductores.$idx.licReverso"));

    //             $idConductor = $c['id_conductor'] ?? null;
    //             $esTitular   = (isset($c['es_titular']) && $c['es_titular'] == "1");

    //             // 2. LÓGICA SEGÚN TIPO DE CONDUCTOR
    //             if ($esTitular) {
    //                 // --- ACTUALIZAR DATOS EN LA RESERVACIÓN (Titular) ---
    //                 DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
    //                     'nombre_cliente'    => $c['nombre'],
    //                     'apellidos_cliente' => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
    //                     'fecha_nacimiento'  => $c['fecha_nacimiento'] ?? null,
    //                     'updated_at'        => now()
    //                 ]);
    //             } else {
    //                 // --- CREAR O ACTUALIZAR CONDUCTOR ADICIONAL ---
    //                 if (empty($idConductor)) {
    //                     $idConductor = DB::table('contrato_conductor_adicional')->insertGetId([
    //                         'id_contrato'      => $idContrato,
    //                         'nombres'          => $c['nombre'],
    //                         'apellidos'        => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
    //                         'numero_licencia'  => $c['numero_licencia'] ?? null,
    //                         'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
    //                         'contacto'         => $c['contacto_emergencia'] ?? null,
    //                         'created_at'       => now(),
    //                         'updated_at'       => now(),
    //                     ]);
    //                 } else {
    //                     DB::table('contrato_conductor_adicional')->where('id_conductor', $idConductor)->update([
    //                         'nombres'          => $c['nombre'],
    //                         'apellidos'        => $c['apellido_paterno'] . ' ' . ($c['apellido_materno'] ?? ''),
    //                         'numero_licencia'  => $c['numero_licencia'] ?? null,
    //                         'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
    //                         'contacto'         => $c['contacto_emergencia'] ?? null,
    //                         'updated_at'       => now()
    //                     ]);
    //                 }
    //             }

    //             // 3. GUARDAR DOCUMENTO: IDENTIFICACIÓN
    //             DB::table('contrato_documento')->updateOrInsert(
    //                 ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'identificacion'],
    //                 [
    //                     'tipo_identificacion'   => $c['tipo_identificacion'] ?? 'INE',
    //                     'numero_identificacion' => $c['numero_identificacion'] ?? null,
    //                     'nombre'                => $c['nombre'],
    //                     'apellido_paterno'      => $c['apellido_paterno'],
    //                     'apellido_materno'      => $c['apellido_materno'] ?? null,
    //                     'fecha_nacimiento'      => $c['fecha_nacimiento'] ?? null,
    //                     'fecha_vencimiento'     => $c['fecha_vencimiento_id'] ?? null,
    //                     // Mantenemos la foto anterior si no se subió una nueva
    //                     'id_archivo_frente'     => $idArchivoFrente ?? DB::raw('id_archivo_frente'),
    //                     'id_archivo_reverso'    => $idArchivoReverso ?? DB::raw('id_archivo_reverso'),
    //                     'updated_at'            => now(),
    //                 ]
    //             );

    //             // 4. GUARDAR DOCUMENTO: LICENCIA
    //             DB::table('contrato_documento')->updateOrInsert(
    //                 ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'licencia'],
    //                 [
    //                     'numero_identificacion' => $c['numero_licencia'] ?? null,
    //                     'pais_emision'          => $c['emite_licencia'] ?? 'México',
    //                     'fecha_emision'         => $c['fecha_emision_licencia'] ?? null,
    //                     'fecha_vencimiento'     => $c['fecha_vencimiento_licencia'] ?? null,
    //                     // Mantenemos la foto anterior si no se subió una nueva
    //                     'id_archivo_frente'     => $idLicFrente ?? DB::raw('id_archivo_frente'),
    //                     'id_archivo_reverso'    => $idLicReverso ?? DB::raw('id_archivo_reverso'),
    //                     'updated_at'            => now(),
    //                 ]
    //             );
    //         }

    //         return response()->json(['success' => true, 'msg' => 'Toda la documentación se guardó correctamente.']);
    //     } catch (\Exception $e) {
    //         Log::error("ERROR guardarDocumentacion Masiva: " . $e->getMessage());
    //         return response()->json(['error' => 'Error interno al guardar.', 'detalle' => $e->getMessage()], 500);
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
            // 🔥 ESCUDO BACKEND: VALIDAR CONTACTO DE EMERGENCIA
            // ====================================================
            foreach ($conductoresData as $c) {
                $esTitular = (isset($c['es_titular']) && $c['es_titular'] == "1");

                if (!$esTitular && !empty($c['contacto_emergencia'])) {
                    $contacto = trim($c['contacto_emergencia']);

                    // Exige estrictamente 10 números
                    if (!preg_match('/^\d{10}$/', $contacto)) {
                        $nombre = $c['nombre'] ?? 'un conductor adicional';
                        return response()->json([
                            'error' => "El teléfono de emergencia de {$nombre} debe tener exactamente 10 dígitos."
                        ], 422);
                    }
                }
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
                // 1. Extraer archivos usando la nomenclatura de puntos
                $idArchivoFrente  = $guardarImagen($request->file("conductores.$idx.idFrente"));
                $idArchivoReverso = $guardarImagen($request->file("conductores.$idx.idReverso"));
                $idLicFrente      = $guardarImagen($request->file("conductores.$idx.licFrente"));
                $idLicReverso     = $guardarImagen($request->file("conductores.$idx.licReverso"));

                $idConductor = $c['id_conductor'] ?? null;
                $esTitular   = (isset($c['es_titular']) && $c['es_titular'] == "1");

                // 2. LÓGICA SEGÚN TIPO DE CONDUCTOR
                if ($esTitular) {
                    // --- ACTUALIZAR DATOS EN LA RESERVACIÓN (Titular) ---
                    // ⚠️ CORRECCIÓN: Quitamos 'fecha_nacimiento' porque no existe en la tabla reservaciones.
                    DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                        'nombre_cliente'    => $c['nombre'],
                        'apellidos_cliente' => trim(($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')),
                        'updated_at'        => now()
                    ]);
                } else {
                    // --- CREAR O ACTUALIZAR CONDUCTOR ADICIONAL ---
                    // ⚠️ CORRECCIÓN: Ajustamos los nombres para que coincidan con tu JS y HTML
                    $datosAdicional = [
                        'nombres'          => $c['nombre'],
                        'apellidos'        => trim(($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')),
                        'numero_licencia'  => $c['numero_licencia'] ?? null,
                        'pais_licencia'    => $c['id_pais'] ?? null, // En tu HTML se llama id_pais
                        'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
                        'contacto'         => $c['contacto_emergencia'] ?? null,
                        'updated_at'       => now()
                    ];

                    if (empty($idConductor)) {
                        $datosAdicional['id_contrato'] = $idContrato;
                        $datosAdicional['created_at']  = now();
                        $idConductor = DB::table('contrato_conductor_adicional')->insertGetId($datosAdicional);
                    } else {
                        DB::table('contrato_conductor_adicional')->where('id_conductor', $idConductor)->update($datosAdicional);
                    }
                }

                // 3. GUARDAR DOCUMENTO: IDENTIFICACIÓN
                // Usamos variables condicionales para las fechas según lo que mande el frontend
                $fechaVencimientoId = $c['fecha_vencimiento_id'] ?? ($c['fecha_vencimiento'] ?? null);

                DB::table('contrato_documento')->updateOrInsert(
                    ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'identificacion'],
                    [
                        'tipo_identificacion'   => $c['tipo_identificacion'] ?? 'ine',
                        'numero_identificacion' => $c['numero_identificacion'] ?? null,
                        'nombre'                => $c['nombre'] ?? null,
                        'apellido_paterno'      => $c['apellido_paterno'] ?? null,
                        'apellido_materno'      => $c['apellido_materno'] ?? null,
                        'fecha_nacimiento'      => $c['fecha_nacimiento'] ?? null,
                        'fecha_vencimiento'     => $fechaVencimientoId,
                        // Mantenemos la foto anterior si no se subió una nueva usando COALESCE
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
                        'pais_emision'          => $c['id_pais'] ?? 'MX', // En tu HTML es id_pais
                        'fecha_emision'         => $c['fecha_emision'] ?? null, // En tu HTML es fecha_emision
                        'fecha_vencimiento'     => $c['fecha_vencimiento'] ?? null,
                        // Mantenemos la foto anterior si no se subió una nueva
                        'id_archivo_frente'     => $idLicFrente ?? DB::raw('id_archivo_frente'),
                        'id_archivo_reverso'    => $idLicReverso ?? DB::raw('id_archivo_reverso'),
                        'updated_at'            => now(),
                    ]
                );
            }

            return response()->json(['success' => true, 'msg' => 'Toda la documentación se guardó correctamente.']);
        } catch (\Exception $e) {
            Log::error("ERROR guardarDocumentacion Masiva: " . $e->getMessage() . " - Línea: " . $e->getLine());
            return response()->json(['error' => 'Error interno al guardar.', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function obtenerDocumentacion($idContrato)
    {
        try {
            $makeUrl = function ($idArchivo) {
                return $idArchivo ? route('archivo.mostrar', ['id' => $idArchivo]) : null;
            };

            $todosDocumentos = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->get()
                ->groupBy('id_conductor');

            // Documentos del titular (id_conductor = null)
            $docsTitular = $todosDocumentos->get(null, collect());
            $ident = $docsTitular->firstWhere('tipo', 'identificacion');
            $lic   = $docsTitular->firstWhere('tipo', 'licencia');

            $licenciaVencida = false;
            if ($lic && $lic->fecha_vencimiento) {
                $licenciaVencida = Carbon::parse($lic->fecha_vencimiento)->isPast();
            }

            $titular = [
                'campos' => [
                    'tipo_identificacion'   => $ident->tipo_identificacion ?? null,
                    'numero_identificacion' => $ident->numero_identificacion ?? null,
                    'nombre'                => $ident->nombre ?? null,
                    'apellido_paterno'      => $ident->apellido_paterno ?? null,
                    'apellido_materno'      => $ident->apellido_materno ?? null,
                    'fecha_nacimiento'      => $ident->fecha_nacimiento ?? null,
                    'fecha_vencimiento_id'  => $ident->fecha_vencimiento ?? null,
                    'numero_licencia'         => $lic->numero_identificacion ?? null,
                    'pais_emision'            => $lic->pais_emision ?? null,
                    'fecha_emision_licencia'  => $lic->fecha_emision ?? null,
                    'fecha_vencimiento_licencia' => $lic->fecha_vencimiento ?? null,
                    'contacto_emergencia' => null
                ],
                'archivos' => [
                    'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
                    'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
                    'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
                    'licReverso_url' => $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
                ],
                'licencia_vencida' => $licenciaVencida,
            ];

            $conductores = DB::table('contrato_conductor_adicional')
                ->where('id_contrato', $idContrato)
                ->get()
                ->keyBy('id_conductor');

            $adicionalesData = [];

            foreach ($todosDocumentos as $idConductor => $grupo) {
                if ($idConductor === null) continue; // Saltamos al titular

                $c = $conductores->get($idConductor);
                if (!$c) continue;

                $ident = $grupo->firstWhere('tipo', 'identificacion');
                $lic   = $grupo->firstWhere('tipo', 'licencia');

                $vencida = false;
                if ($lic && $lic->fecha_vencimiento) {
                    $vencida = Carbon::parse($lic->fecha_vencimiento)->isPast();
                }

                $adicionalesData[$idConductor] = [
                    'campos' => [
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
                    ],
                    'archivos' => [
                        'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
                        'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
                        'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
                        'licReverso_url' => $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
                    ],
                    'licencia_vencida' => $vencida,
                ];
            }

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
            $res = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
            if (!$res) return response()->json(['ok' => false, 'msg' => 'No encontrada']);

            $inicio = Carbon::parse($res->fecha_inicio);
            $fin = Carbon::parse($res->fecha_fin);
            $dias = max(1, $inicio->diffInDays($fin));

            $tarifa = ($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base;
            $baseTotal = $tarifa * $dias;

            $serviciosData = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->where('rs.id_reservacion', $idReservacion)
                ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario', 's.tipo_cobro')
                ->get();

            $listaAdicionales = [];
            $totalServicios = 0;

            foreach ($serviciosData as $s) {
                $sub = ($s->tipo_cobro === 'por_dia')
                    ? ($s->cantidad * $s->precio_unitario * $dias)
                    : ($s->cantidad * $s->precio_unitario);

                $totalServicios += $sub;
                $listaAdicionales[] = [
                    'nombre' => $s->nombre,
                    'cantidad' => $s->cantidad,
                    'total' => $sub
                ];
            }

            $delivery = floatval($res->delivery_total ?? 0);
            if ($res->delivery_activo && $delivery > 0) {
                $listaAdicionales[] = [
                    'nombre' => 'Servicio de Delivery',
                    'cantidad' => 1,
                    'total' => $delivery
                ];
            }

            $precioSeguros = floatval($this->calcularTotalProtecciones($idReservacion));

            $nombreSeguro = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as p', 'rps.id_paquete', '=', 'p.id_paquete')
                ->where('rps.id_reservacion', $idReservacion)
                ->value('p.nombre') ?? 'Protecciones seleccionadas';

            $codigoCategoria = DB::table('categorias_carros')
                ->where('id_categoria', $res->id_categoria)
                ->value('codigo') ?? 'C';

            $garantia = $this->obtenerGarantiaSeguro($codigoCategoria, $nombreSeguro);

            $subtotal = $baseTotal + $totalServicios + $delivery + $precioSeguros;
            $iva = $subtotal * 0.16;
            $totalContrato = $subtotal + $iva;

            $reservacionActual = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first(['subtotal', 'impuestos', 'total']);

            if (
                !$reservacionActual ||
                abs($reservacionActual->total - $totalContrato) > 0.01
            ) {

                DB::table('reservaciones')
                    ->where('id_reservacion', $idReservacion)
                    ->update([
                        'subtotal' => $subtotal,
                        'impuestos' => $iva,
                        'total' => $totalContrato,
                        'updated_at' => now(),
                    ]);
            }

            $pagos = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->where('estatus', 'paid')
                ->orderByDesc('created_at')
                ->get();

            $totalPagado = $this->pagosReservacionQuery($idReservacion)->sum('monto');
            $totalGarantiaPagada = $this->pagosGarantiaQuery($idReservacion)->sum('monto');

            $saldoPendiente = $totalContrato - $totalPagado;
            $garantiaPendiente = max(0, ($garantia['monto'] ?? 0) - $totalGarantiaPagada);

            return response()->json([
                'success' => true,
                'data' => [
                    'base' => [
                        'total' => $baseTotal,
                        'descripcion' => "{$dias} días · $" . number_format($tarifa, 2)
                    ],
                    'adicionales' => [
                        'lista' => $listaAdicionales,
                        'total' => $totalServicios + $delivery
                    ],
                    'totales' => [
                        'nombre_seguro' => $nombreSeguro,
                        'monto_seguros' => $precioSeguros,
                        'subtotal' => $subtotal,
                        'iva' => $iva,
                        'total_contrato' => $totalContrato,
                        'saldo_pendiente' => $saldoPendiente,
                        'garantia' => $garantia,
                    ],
                    'pagos' => [
                        'realizados' => $totalPagado,
                        'saldo'      => $saldoPendiente,
                        'garantia'   => [
                            'realizados' => $totalGarantiaPagada,
                            'pendiente'  => $garantiaPendiente,
                        ],
                        'lista'      => $pagos // Esta es la colección de la base de datos
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function agregarPagoPaso6(Request $request)
    {
        try {
            // validacion
            $data = $request->validate([
                'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
                'tipo_pago'      => 'required|string|max:50',
                'monto'          => 'required|numeric|min:0.01',
                'ultimos4'       => 'nullable|string|max:10',
                'auth'           => 'nullable|string|max:50',
                'notas'          => 'nullable|string|max:500',
                'metodo'         => 'nullable|string|max:50',
                'origen'         => 'nullable|string|max:50',
                'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            // Normalizar Método y Origen
            $metodo = strtoupper($data['metodo'] ?? 'EFECTIVO');
            $origen = strtolower($data['origen'] ?? 'mostrador');

            //  Subir el comprobante si el frontend lo envió
            $filePath = null;
            if ($request->hasFile('comprobante')) {
                $filePath = $request->file('comprobante')->store('pagos', 'public');
            }

            // Iniciamos transacción para evitar pagos a medias
            DB::beginTransaction();

            // 4. Insertar el pago
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion' => $data['id_reservacion'],
                'id_contrato'    => null,

                'origen_pago'    => $origen,
                'metodo'         => $metodo,
                'tipo_pago'      => $data['tipo_pago'],

                'monto'          => $data['monto'],
                'moneda'         => 'MXN',
                'estatus'        => 'paid',

                'comprobante'    => $filePath,

                'payload_webhook' => json_encode([
                    'ultimos4' => $data['ultimos4'] ?? null,
                    'auth'     => $data['auth'] ?? null,
                    'notas'    => $data['notas'] ?? null,
                ]),

                'captured_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Verificar si con este pago el saldo ya es cero para actualizar la reservación
            $this->sincronizarEstadoPago($data['id_reservacion']);

            DB::commit();

            // Retornamos el ok al JS para que recargue la tablita de pagos
            return response()->json(['ok' => true, 'id_pago' => $idPago]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error agregarPagoPaso6: " . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error interno al guardar el pago'], 500);
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

            $tipoPago = strtoupper(trim($req->input('tipo_pago', 'PAGO RESERVACION')));

            // Crear el registro de pago
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion'       => $req->id_reservacion,
                'id_contrato'          => null,

                'origen_pago'          => 'online',
                'pasarela'             => 'paypal',
                'referencia_pasarela'  => $req->order_id,

                'estatus'              => 'paid',
                'metodo'               => 'PayPal',
                'tipo_pago'            => $tipoPago,
                'tipo_pago'            => 'PAGO RESERVACIÓN',

                'tipo_pago'            => $tipoPago,
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
                ->update(['paypal_order_id' => $req->order_id]);

            $this->sincronizarEstadoPago($req->id_reservacion);

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
            $pago = DB::table('pagos')->where('id_pago', $idPago)->first();

            if (!$pago) {
                return response()->json(['success' => false, 'msg' => 'Pago no encontrado'], 404);
            }

            DB::transaction(function () use ($idPago, $pago) {
                DB::table('pagos')->where('id_pago', $idPago)->delete();
                $this->sincronizarEstadoPago($pago->id_reservacion);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
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

            $tipoPago = strtoupper(trim($req->tipo_pago));

            // 3) Insertar el pago
            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion' => $req->id_reservacion,
                'id_contrato'    => null,
                'origen_pago'    => $origen, // Guardará el nombre bonito
                'metodo'         => strtoupper($req->metodo),
                'tipo_pago'      => $tipoPago,
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
            $this->sincronizarEstadoPago($req->id_reservacion);

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
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if (!$reservacion) {
                return redirect()->back()->with('error', 'Reservación no encontrada.');
            }

            $contratoExistente = DB::table('contratos')
                ->where('id_reservacion', $idReservacion)
                ->first();

            if ($contratoExistente) {
                return redirect()->route('contrato.final', $contratoExistente->id_contrato);
            }

            $idContrato = DB::transaction(function () use ($idReservacion) {
                $nuevoId = DB::table('contratos')->insertGetId([
                    'id_reservacion'  => $idReservacion,
                    'id_asesor'       => session('id_usuario') ?? null,
                    'numero_contrato' => 'TEMP',
                    'estado'          => 'abierto',
                    'abierto_en'      => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $folio = str_pad($nuevoId, 4, '0', STR_PAD_LEFT);

                DB::table('contratos')
                    ->where('id_contrato', $nuevoId)
                    ->update(['numero_contrato' => $folio]);

                return $nuevoId;
            });

            return redirect()->route('contrato.final', $idContrato);
        } catch (\Exception $e) {
            Log::error("Error en finalizar Contrato2: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al finalizar contrato.');
        }
    }

    private function sincronizarEstadoPago($idReservacion)
    {
        $res = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();

        $totalPagado = $this->pagosReservacionQuery($idReservacion)->sum('monto');

        $saldo = $res->total - $totalPagado;

        if ($saldo <= 0) {
            $nuevoEstadoPago = 'Pagado';
            $nuevoEstadoReserva = 'confirmada';
        } elseif ($totalPagado > 0) {
            $nuevoEstadoPago = 'Parcial';
            $nuevoEstadoReserva = $res->estado;
        } else {
            $nuevoEstadoPago = 'Pendiente';
            $nuevoEstadoReserva = 'pendiente_pago';
        }

        // Actualizar una sola vez
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'status_pago' => $nuevoEstadoPago,
                'estado'      => $nuevoEstadoReserva,
                'updated_at'  => now(),
            ]);
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

    public function validarDocumentoMaestro(Request $request)
    {
        $request->validate([
            'tipo' => 'nullable|string|max:20',
            'numero' => 'nullable|string|max:50',
            'id_pais' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_emision' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $tipo = strtolower($request->input('tipo', 'licencia'));
        $numeroRaw = $request->input('numero');
        $numero = strtoupper(trim($numeroRaw));

        $paisRaw = strtoupper(trim($request->input('id_pais')));
        $fNacimiento = $request->input('fecha_nacimiento');
        $fEmision = $request->input('fecha_emision');
        $fVencimiento = $request->input('fecha_vencimiento');

        // =========================
        // NORMALIZACIÓN PAÍS
        // =========================
        $pais = match ($paisRaw) {
            '1', 'MX', 'MEXICO', 'MÉXICO' => 'MX',
            '2', 'US', 'USA', 'U.S.A.', 'U.S.A', 'UNITED STATES' => 'US',
            '3', 'BR', 'BRASIL', 'BRAZIL' => 'BR',
            '4', 'CO', 'COLOMBIA' => 'CO',
            '5', 'CA', 'CANADA', 'CANADÁ' => 'CA',
            default => 'MX'
        };

        $res = [
            'success' => true,
            'status' => 'ok',
            'status_edad' => 'adulto',
            'edad' => 0,
            'msg' => []
        ];

        $prioridad = [
            'ok' => 0,
            'warning' => 1,
            'invalido' => 2,
            'vencido' => 3,
            'error_fecha' => 4,
            'prohibido' => 5
        ];

        $setStatus = function ($nuevo) use (&$res, $prioridad) {
            if ($prioridad[$nuevo] > $prioridad[$res['status']]) {
                $res['status'] = $nuevo;
            }
        };

        $hoy = \Carbon\Carbon::now()->startOfDay();

        // =========================
        // VALIDAR NÚMERO OBLIGATORIO
        // =========================
        if (!$numero) {
            $setStatus('invalido');
            $res['msg'][] = "Número de documento requerido.";
        }

        // =========================
        // PARSEO SEGURO
        // =========================
        try {
            $nacimiento = $fNacimiento ? \Carbon\Carbon::parse($fNacimiento) : null;
            $emision = $fEmision ? \Carbon\Carbon::parse($fEmision) : null;
            $vence = $fVencimiento ? \Carbon\Carbon::parse($fVencimiento) : null;
        } catch (\Exception $e) {
            $setStatus('error_fecha');
            $res['msg'][] = "Formato de fecha inválido.";
            return response()->json($res);
        }

        // =========================
        // VALIDACIONES DE FECHAS Y EDAD
        // =========================
        if ($nacimiento && $nacimiento->isAfter($hoy)) {
            $setStatus('error_fecha');
            $res['msg'][] = "Fecha de nacimiento futura.";
        }

        if ($nacimiento) {
            $edad = $nacimiento->age;
            $res['edad'] = $edad;

            if ($edad < 18) {
                $setStatus('prohibido');
                $res['status_edad'] = 'prohibido';
                $res['msg'][] = "Debe ser mayor de 18 años.";
                return response()->json($res);
            }

            if ($edad < 24) {
                $res['status_edad'] = 'menor';
                $res['msg'][] = "Conductor joven.";
            }
        }

        if ($emision && $emision->isAfter($hoy)) {
            $setStatus('error_fecha');
            $res['msg'][] = "Fecha de emisión futura inválida.";
        }

        if ($nacimiento && $emision) {
            $edadEmision = $nacimiento->diffInYears($emision);
            if ($edadEmision < 16) {
                $setStatus('prohibido');
                $res['msg'][] = "Licencia emitida siendo menor de edad.";
            }
        }

        $esPermanente = !$vence || ($vence && $vence->year >= 2090);

        if (!$esPermanente && $vence && $vence->isBefore($hoy)) {
            $setStatus('vencido');
            $res['msg'][] = "Documento expirado.";
        }

        if ($emision && $vence) {
            if ($vence->lte($emision)) {
                $setStatus('error_fecha');
                array_unshift($res['msg'], "El vencimiento no puede ser anterior a la emisión.");
            }

            // 🔥 CORRECCIÓN: Redondeamos para no fallar por diferencias de meses o años bisiestos
            $duracion = round($emision->floatDiffInYears($vence));

            if (!$esPermanente) {
                $esVigenciaInusual = false;
                $esErrorFatal = false; // Controla si es bloqueo (Rojo) o solo aviso (Amarillo)
                $motivoVigencia = "";

                switch ($pais) {
                    case 'MX':
                        if ($duracion > 3) {
                            $esVigenciaInusual = true;
                            $esErrorFatal = false; // MX se queda como Warning (Amarillo)
                            $motivoVigencia = "Máximo 3 años permitidos.";
                        }
                        break;
                    case 'CA':
                        if ($duracion != 5) {
                            $esVigenciaInusual = true;
                            $esErrorFatal = true; // 🛑 Bloqueo
                            $motivoVigencia = "Debe ser exactamente de 5 años.";
                        }
                        break;
                    case 'US':
                        if ($duracion > 8) {
                            $esVigenciaInusual = true;
                            $esErrorFatal = true; // 🛑 Bloqueo
                            $motivoVigencia = "Máximo 8 años permitidos.";
                        }
                        break;
                    case 'CO':
                        if ($duracion > 10) {
                            $esVigenciaInusual = true;
                            $esErrorFatal = true; // 🛑 Bloqueo
                            $motivoVigencia = "Máximo 10 años permitidos.";
                        }
                        break;
                    case 'BR':
                        // 🔥 CORRECCIÓN: Usar la edad al momento de la emisión, no la edad actual
                        $edadAlEmitir = ($nacimiento && $emision) ? $nacimiento->diffInYears($emision) : ($res['edad'] ?? 30);

                        // En Brasil, si la sacaste teniendo 50 o más, dura 5. Si tenías menos de 50, dura 10.
                        $limiteBr = ($edadAlEmitir >= 50) ? 5 : 10;

                        if ($duracion > $limiteBr) {
                            $esVigenciaInusual = true;
                            $esErrorFatal = true; // 🛑 Bloqueo
                            $motivoVigencia = "Emitida a los {$edadAlEmitir} años, máximo {$limiteBr} años.";
                        }
                        break;
                }

                if ($esVigenciaInusual) {
                    if ($esErrorFatal) {
                        $setStatus('error_fecha'); // Lanza error fatal que BORRA el input en JS
                        $res['msg'][] = "Vigencia inválida para {$pais}: {$motivoVigencia}";
                    } else {
                        $setStatus('warning'); // Solo pinta amarillo y avisa
                        $res['msg'][] = "Vigencia inusual para {$pais}: {$motivoVigencia}";
                    }
                } elseif ($duracion < 1) {
                    $setStatus('warning');
                    $res['msg'][] = "Vigencia demasiado corta (menor a 1 año).";
                }
            }
        }

        // =========================
        // VALIDACIÓN DE NÚMERO Y FRAUDE
        // =========================
        if ($numero) {
            // Limpiezas diferenciadas
            $alfanumerico = preg_replace('/[^A-Z0-9]/', '', $numero); // Mantiene letras (Para CURP/MX/US)
            $soloNumeros = preg_replace('/[^\d]/', '', $numero);      // Solo dígitos (Para Brasil/Colombia)

            $valido = true;
            $errorMsg = "";

            // 1. Detección de patrones sospechosos
            if (
                preg_match('/^(.)\1+$/', $alfanumerico) ||
                in_array($alfanumerico, ['123456789', '1234567890', '0123456789', '987654321'])
            ) {
                $valido = false;
                $errorMsg = "Número sospechoso o de prueba.";
            }

            if ($valido) {
                switch ($tipo) {
                    case 'ine':
                    case 'ife':
                        $regexCURP = '/^[A-Z]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[A-Z]{3}[A-Z0-9]\d$/';
                        $esCURP = preg_match($regexCURP, $alfanumerico);

                        $esClave = preg_match('/^[A-Z]{6}\d{8}[HM]\d{3}$/', $alfanumerico);

                        if (!$esCURP && !$esClave) {
                            $valido = false;
                            if (strlen($alfanumerico) !== 18) {
                                $errorMsg = "El CURP o Clave debe tener exactamente 18 caracteres.";
                            } else {
                                $errorMsg = "Formato de CURP/Clave inválido (revisa fecha, estado o día).";
                            }
                        }

                        if ($esCURP && $nacimiento) {
                            $fechaCurp = substr($alfanumerico, 4, 6);
                            $fechaInput = $nacimiento->format('ymd');

                            if ($fechaCurp !== $fechaInput) {
                                $setStatus('warning');
                                $res['msg'][] = "La fecha de nacimiento no coincide con el CURP ingresado.";
                            }
                        }

                        break;

                    case 'pasaporte':
                        if (strlen($alfanumerico) < 6 || strlen($alfanumerico) > 15 || preg_match('/^(.)\1+$/', $alfanumerico)) {
                            $valido = false;
                            $errorMsg = "El pasaporte es inválido o demasiado corto.";
                        }
                        break;

                    case 'cedula':
                        if (!preg_match('/^\d{7,8}$/', $numero)) {
                            $valido = false;
                            $errorMsg = "La Cédula Profesional debe ser de 7 u 8 números.";
                        }
                        break;

                    case 'licencia':
                    default:
                        $valido = match ($pais) {
                            // México: Alfanumérico
                            'MX' => preg_match('/^[A-Z]{3}\d{5,7}$/', $alfanumerico),
                            // USA: Letra + 7 números
                            'US' => preg_match('/^[A-Z]\d{7}$/', $alfanumerico),
                            // Canadá: Formato con guiones (usamos el $numero original sin limpiar)
                            'CA' => preg_match('/^[A-Z]\d{4}-\d{5}-\d{4}$/', $numeroRaw),
                            // Colombia: Solo números
                            'CO' => preg_match('/^\d{7,12}$/', $soloNumeros),
                            // Brasil: Algoritmo matemático (Módulo 11)
                            'BR' => preg_match('/^\d{9,11}$/', $soloNumeros),
                            default => strlen($alfanumerico) >= 5
                        };

                        if (!$valido) {
                            $errorMsg = "Error: El número [$numero] no cumple reglas de [$pais]";
                        }
                        break;
                }
            }

            if (!$valido) {
                $setStatus('invalido');
                // Usamos unshift para que este error sea el primero que vea el usuario
                array_unshift($res['msg'], $errorMsg);
            }
        }


        return response()->json($res);
    }

    public function guardarFirmaCliente(Request $request)
    {
        try {
            $request->validate([
                'id_contrato' => 'required|integer|exists:contratos,id_contrato',
                'firma'       => 'required|string', // Debe ser la cadena Base64
            ]);

            DB::table('contratos')
                ->where('id_contrato', $request->id_contrato)
                ->update([
                    'firma_cliente' => $request->firma,
                    'updated_at'    => now()
                ]);

            return response()->json([
                'ok' => true,
                'msg' => 'Firma guardada correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al guardar firma en Paso 5: " . $e->getMessage());
            return response()->json([
                'ok' => false,
                'msg' => 'Error interno al guardar la firma.'
            ], 500);
        }
    }
}
