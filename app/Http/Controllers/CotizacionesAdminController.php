<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\CotizacionAdminMail;

class CotizacionesAdminController extends Controller
{
    /**
     * 🚗 Obtener todas las categorías para el modal (AJAX)
     * EXACTAMENTE COMO EN RESERVACIONES
     */
    public function getCategorias()
    {
        try {
            $categorias = DB::table('categorias_carros as c')
                ->join('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
                ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
                ->where('ck.activo', 1)
                ->select(
                    'c.id_categoria',
                    'c.codigo',
                    'c.nombre',
                    'c.descripcion',
                    'c.precio_dia',
                    'c.activo',
                    'ck.costo_km',
                    DB::raw('MAX(v.capacidad_tanque) as litros_maximos')
                )
                ->groupBy('c.id_categoria', 'c.codigo', 'c.nombre', 'c.descripcion', 'c.precio_dia', 'c.activo', 'ck.costo_km')
                ->orderBy('c.precio_dia')
                ->get();

            return response()->json($categorias);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener categorías: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $ciudades = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderBy('nombre')
            ->get();

        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.activo', 1)
            ->select(
                's.id_sucursal',
                's.nombre as sucursal',
                'c.nombre as ciudad',
                'c.id_ciudad'
            )
            ->orderByRaw("CASE WHEN c.nombre = 'Querétaro' THEN 0 ELSE 1 END")
            ->orderBy('c.nombre')
            ->orderBy('s.nombre')
            ->get()
            ->groupBy('ciudad');

        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre', 'descripcion', 'precio_dia')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $ubicaciones = DB::table('ubicaciones_servicio')
            ->where('activo', 1)
            ->orderBy('estado')
            ->orderBy('destino')
            ->get();

        // ✅ Obtener protecciones individuales (igual que en reservaciones)
        $individuales = DB::table('seguro_individuales')
            ->select('id_individual', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
            ->where('activo', 1)
            ->orderBy('precio_por_dia')
            ->get();

        // Normalizador de texto
        $norm = function ($s) {
            $s = mb_strtolower(trim((string)$s));
            $s = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
                ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
                $s
            );
            return $s;
        };

        $match = function ($row, array $keys) use ($norm) {
            $text = $norm(($row->nombre ?? '') . ' ' . ($row->descripcion ?? ''));
            foreach ($keys as $k) {
                if (str_contains($text, $norm($k))) {
                    return true;
                }
            }
            return false;
        };

        // Agrupar por categorías
        $grupo_colision = $individuales->filter(fn($r) => $match($r, [
            'LDW',
            'PDW',
            'CDW',
            'collision',
            'damage waiver',
            'loss damage',
            'robo',
            'theft',
            'decline cdw'
        ]))->values();

        $grupo_medicos = $individuales->filter(fn($r) => $match($r, [
            'PAI',
            'personal accident',
            'gastos medicos',
            'medico',
            'medical'
        ]))->values();

        $grupo_asistencia = $individuales->filter(fn($r) => $match($r, [
            'PRA',
            'road assistance',
            'asistencia',
            'carretera',
            'camino'
        ]))->values();

        $grupo_terceros = $individuales->filter(fn($r) => $match($r, [
            'LI',
            'liability',
            'responsabilidad civil',
            'terceros'
        ]))->values();

        $idsUsados = collect()
            ->merge($grupo_colision->pluck('id_individual'))
            ->merge($grupo_medicos->pluck('id_individual'))
            ->merge($grupo_asistencia->pluck('id_individual'))
            ->merge($grupo_terceros->pluck('id_individual'))
            ->unique();

        $grupo_protecciones = $individuales
            ->filter(fn($r) => !$idsUsados->contains($r->id_individual))
            ->values();

        return view('Admin.Cotizar', compact(
            'ciudades',
            'sucursales',
            'categorias',
            'ubicaciones',
            'grupo_colision',
            'grupo_medicos',
            'grupo_asistencia',
            'grupo_terceros',
            'grupo_protecciones'
        ));
    }

    /**
     * 🛡️ Obtener paquetes de seguros activos (Cotizar)
     */
    public function getSeguros()
    {
        try {
            $seguros = DB::table('seguro_paquete')
                ->select('id_paquete', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
                ->where('activo', true)
                ->orderBy('precio_por_dia')
                ->get();

            return response()->json($seguros);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener seguros: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🧩 Obtener servicios adicionales activos (Cotizar)
     */
    public function getServicios()
    {
        try {
            $servicios = DB::table('servicios')
                ->select('id_servicio', 'nombre', 'descripcion', 'tipo_cobro', 'precio', 'activo')
                ->where('activo', true)
                ->orderBy('precio')
                ->get();

            return response()->json($servicios);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener servicios: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🚗 Obtener información de una categoría (AJAX)
     */
    public function getCategoria($idCategoria)
    {
        try {
            $cat = DB::table('categorias_carros')
                ->select('id_categoria', 'nombre', 'descripcion', 'precio_dia', 'activo')
                ->where('id_categoria', $idCategoria)
                ->where('activo', true)
                ->first();

            if (!$cat) {
                return response()->json(['error' => true, 'message' => 'Categoría no encontrada'], 404);
            }

            // 🖼️ Imagen por nombre de categoría (opcional)
            $cat->imagen = asset('img/categorias/' . Str::slug($cat->nombre) . '.jpg');

            return response()->json($cat);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener categoría: " . $e->getMessage());
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 💾 Guardar cotización / enviar / confirmar
     */
    public function guardarCotizacion(Request $request)
    {
        try {
            // ✅ Validación
            $validated = $request->validate([
                'pickup_sucursal_id' => 'required|integer|exists:sucursales,id_sucursal',
                'dropoff_sucursal_id' => 'required|integer|exists:sucursales,id_sucursal',
                'pickup_date'   => 'required|date',
                'pickup_time'   => 'required|string|max:10',
                'dropoff_date'  => 'required|date|after_or_equal:pickup_date',
                'dropoff_time'  => 'required|string|max:10',
                'categoria_id'  => 'required|integer|exists:categorias_carros,id_categoria',
                'total'         => 'nullable|numeric',
                'servicios'     => 'nullable|array',
            ]);

            // 🎫 Folio único
            $folio = 'COT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            // 🧮 Cálculo de días y totales
            $dias = max(1, Carbon::parse($request->pickup_date)->diffInDays(Carbon::parse($request->dropoff_date)));
            $iva = round(($request->input('subtotal', 0) ?? 0) * 0.16, 2);
            $total = $request->input('total', 0);

            // 🔍 Datos de sucursales
            $sucursalRetiro = DB::table('sucursales')->where('id_sucursal', $request->pickup_sucursal_id)->first();
            $sucursalEntrega = DB::table('sucursales')->where('id_sucursal', $request->dropoff_sucursal_id)->first();

            $pickup_name = $sucursalRetiro?->nombre ?? '';
            $dropoff_name = $sucursalEntrega?->nombre ?? '';

            // 🔍 Datos de categoría
            $categoria = DB::table('categorias_carros')
                ->select('nombre', 'descripcion', 'precio_dia')
                ->where('id_categoria', $request->categoria_id)
                ->first();

            // 🖼️ Imagen por categoría (para PDF: mejor ruta física)
            $imgCategoria = public_path('img/categorias/' . Str::slug($categoria->nombre) . '.jpg');

            $servicios = $request->input('servicios', []);

            // Calcular total de servicios
            $serviciosTotal = 0;
            if (!empty($servicios)) {
                if (isset($servicios['dropoff']) && ($servicios['dropoff']['activo'] ?? false)) {
                    $serviciosTotal += (float)($servicios['dropoff']['total'] ?? 0);
                }
                if (isset($servicios['delivery']) && ($servicios['delivery']['activo'] ?? false)) {
                    $serviciosTotal += (float)($servicios['delivery']['total'] ?? 0);
                }
                if (isset($servicios['gasolina']) && ($servicios['gasolina']['activo'] ?? false)) {
                    $serviciosTotal += (float)($servicios['gasolina']['total'] ?? 0);
                }
            }

            // 💾 Insertar cotización
            $idCotizacion = DB::table('cotizaciones')->insertGetId([
                'folio'              => $folio,
                'id_categoria'       => $request->categoria_id,
                'categoria_nombre'   => $categoria->nombre ?? '',
                'pickup_date'        => $request->pickup_date,
                'pickup_time'        => $request->pickup_time,
                'pickup_name'        => $pickup_name,
                'dropoff_date'       => $request->dropoff_date,
                'dropoff_time'       => $request->dropoff_time,
                'dropoff_name'       => $dropoff_name,
                'days'               => $dias,

                // 💰 Totales y tarifas
                'tarifa_base'        => $request->input('tarifa_base', $categoria->precio_dia ?? 0),
                'tarifa_modificada'  => $request->filled('tarifa_modificada') ? $request->tarifa_modificada : null,
                'tarifa_ajustada'    => $request->boolean('tarifa_ajustada', false),
                'extras_sub'         => $request->input('extras_sub', 0),
                'servicios_total'    => $serviciosTotal,
                'iva'                => $iva,
                'total'              => $total,

                // 🧩 JSON
                'addons'             => json_encode($request->input('extras', [])),
                'seguro'             => json_encode($request->input('seguro', [])),
                'servicios'          => json_encode($servicios),
                'cliente'            => json_encode($request->input('cliente', [])),

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 🧾 Variables auxiliares
            $cliente = (object) $request->input('cliente', []);
            $extras = $request->input('extras', []);
            $seguro = $request->input('seguro', null);
            $accion = 'guardada';

            // 🧾 Lista de servicios seleccionados
            $extrasList = '';
            if ($seguro) {
                $extrasList .= "<li>Protección: {$seguro['nombre']} - $" . number_format($seguro['precio'], 2) . " MXN/día</li>";
            }
            if (!empty($extras)) {
                foreach ($extras as $e) {
                    $extrasList .= "<li>{$e['cantidad']}× {$e['nombre']} - $" . number_format($e['precio'], 2) . " MXN</li>";
                }
            }
            if ($extrasList === '') $extrasList = '<li>Sin adicionales</li>';

            /* ==========================================================
               📄 Generar PDF (USANDO VISTA Admin.cotizacion-pdf)
            ========================================================== */
            $publicPath = public_path('storage/cotizaciones');
            if (!file_exists($publicPath)) mkdir($publicPath, 0777, true);

            $filePath = $publicPath . '/' . $folio . '.pdf';

            $tarifaDiaria = $request->filled('tarifa_modificada')
                ? $request->tarifa_modificada
                : ($categoria->precio_dia ?? 0);

            $pdf = Pdf::loadView('Admin.cotizacion-pdf', [
                'logoPath'     => public_path('img/Logo3.jpg'),
                'folio'        => $folio,
                'fechaHoy'     => now()->format('d M Y'),

                'pickup_name'  => $pickup_name,
                'pickup_date'  => $request->pickup_date,
                'pickup_time'  => $request->pickup_time,

                'dropoff_name' => $dropoff_name,
                'dropoff_date' => $request->dropoff_date,
                'dropoff_time' => $request->dropoff_time,

                'dias'         => $dias,
                'categoria'    => $categoria,
                'imgCategoria' => $imgCategoria,
                'tarifaDiaria' => $tarifaDiaria,
                'extrasList'   => $extrasList,
                'iva'          => $iva,
                'total'        => $total,
            ])->setPaper('a4', 'portrait');

            file_put_contents($filePath, $pdf->output());

            /* ==========================================================
               📧 Enviar correo con PDF adjunto (USANDO MAILABLE)
            ========================================================== */
            if ($request->has('enviarCorreo') && !empty($cliente->email)) {

                $clienteNombre = trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellidos ?? ''));

                Mail::to($cliente->email)->send(
                    new \App\Mail\CotizacionAdminMail($clienteNombre, $folio, $filePath)
                );

                $accion = 'enviada por correo';
            }

            /* ==========================================================
               ✅ Confirmar → crear reservación (CON SERVICIOS)
            ========================================================== */
            if ($request->has('confirmar')) {

                $idReserva = DB::table('reservaciones')->insertGetId([
                    'codigo'           => 'RES-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'id_categoria'      => $request->categoria_id,
                    'fecha_inicio'      => $request->pickup_date,
                    'fecha_fin'         => $request->dropoff_date,
                    'hora_retiro'       => $request->pickup_time,
                    'hora_entrega'      => $request->dropoff_time,
                    'sucursal_retiro'   => $request->pickup_sucursal_id,
                    'sucursal_entrega'  => $request->dropoff_sucursal_id,

                    'ciudad_retiro'     => $sucursalRetiro->id_ciudad ?? null,
                    'ciudad_entrega'    => $sucursalEntrega->id_ciudad ?? null,

                    'tarifa_base'        => $request->input('tarifa_base', $categoria->precio_dia ?? 0),
                    'tarifa_modificada'  => $request->input('tarifa_modificada', $request->input('tarifa_base', $categoria->precio_dia ?? 0)),
                    'tarifa_ajustada'    => $request->boolean('tarifa_ajustada', false),

                    'subtotal'         => $total / 1.16,
                    'impuestos'        => $total - ($total / 1.16),
                    'total'            => $total,
                    'moneda'           => 'MXN',
                    'estado'           => 'pendiente_pago',
                    'nombre_cliente'   => $cliente->nombre ?? null,
                    'email_cliente'    => $cliente->email ?? null,
                    'telefono_cliente' => $cliente->telefono ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);


                // Drop Off (id_servicio = 11)
                if (!empty($servicios['dropoff']) && ($servicios['dropoff']['activo'] ?? false)) {
                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $idReserva,
                        'id_servicio'     => 11,
                        'cantidad'        => 1,
                        'precio_unitario' => $servicios['dropoff']['total'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                // Delivery (id_servicio = 12)
                if (!empty($servicios['delivery']) && ($servicios['delivery']['activo'] ?? false)) {
                    DB::table('reservaciones')
                        ->where('id_reservacion', $idReserva)
                        ->update([
                            'delivery_activo'    => 1,
                            'delivery_total'     => $servicios['delivery']['total'] ?? 0,
                            'delivery_km'        => $servicios['delivery']['km'] ?? 0,
                            'delivery_direccion' => $servicios['delivery']['direccion'] ?? '',
                            'delivery_ubicacion' => $servicios['delivery']['ubicacion'] ?? null,
                            'delivery_precio_km' => $servicios['delivery']['precio_km'] ?? 0,
                        ]);
                }

                // Gasolina Prepago (id_servicio = 1)
                if (!empty($servicios['gasolina']) && ($servicios['gasolina']['activo'] ?? false)) {
                    $capacidadMax = DB::table('vehiculos')
                        ->where('id_categoria', $request->categoria_id)
                        ->max('capacidad_tanque') ?? 0;

                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $idReserva,
                        'id_servicio'     => 1,
                        'cantidad'        => $capacidadMax,
                        'precio_unitario' => 20.00,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                // ✅ Guardar adicionales normales (extras)
                if (!empty($request->input('extras', []))) {
                    foreach ($request->input('extras') as $extra) {
                        DB::table('reservacion_servicio')->insert([
                            'id_reservacion'  => $idReserva,
                            'id_servicio'     => $extra['id'],
                            'cantidad'        => $extra['cantidad'] ?? 1,
                            'precio_unitario' => $extra['precio'] ?? 0,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                // ✅ Guardar seguro (protección)
                if ($seguro = $request->input('seguro', null)) {
                    DB::table('reservacion_paquete_seguro')->insert([
                        'id_reservacion' => $idReserva,
                        'id_paquete'     => $seguro['id'],
                        'precio_por_dia' => $seguro['precio'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                $accion = 'confirmada y registrada como reservación';
            }

            return response()->json([
                'success' => true,
                'accion'  => $accion,
                'folio'   => $folio,
                'id'      => $idCotizacion,
                'message' => "Cotización {$accion} correctamente.",
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error en guardarCotizacion: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la cotización.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 📋 Vista de listado de cotizaciones (temporal)
     */
    public function listado()
    {
        // 🔹 Obtener todas las cotizaciones (últimas primero)
        $cotizaciones = DB::table('cotizaciones')
            ->orderByDesc('id_cotizacion')
            ->get();

        // 🔹 Enviar los datos a la vista
        return view('Admin.CotizacionesListado', compact('cotizaciones'));
    }

    public function convertirAReservacion($id)
    {
        try {
            Log::info("🔄 [Convertir] Iniciando conversión de cotización ID {$id}");

            // 1️⃣ Buscar la cotización
            $cot = DB::table('cotizaciones')->where('id_cotizacion', $id)->first();

            if (!$cot) {
                Log::warning("⚠️ [Convertir] Cotización no encontrada ID {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada.'
                ], 404);
            }

            Log::info("📦 [Convertir] Cotización encontrada: {$cot->folio}");

            // 2️⃣ Decodificar JSON
            $cliente = json_decode($cot->cliente ?? '{}', true);
            $addons = json_decode($cot->addons ?? '[]', true);
            $seguro = json_decode($cot->seguro ?? '{}', true);
            $servicios = json_decode($cot->servicios ?? '{}', true);

            // 3️⃣ Buscar sucursales por nombre
            $sucursalRetiro = DB::table('sucursales')
                ->where('nombre', 'LIKE', "%{$cot->pickup_name}%")
                ->value('id_sucursal');

            $sucursalEntrega = DB::table('sucursales')
                ->where('nombre', 'LIKE', "%{$cot->dropoff_name}%")
                ->value('id_sucursal');

            Log::info("🏬 Sucursal retiro ID: {$sucursalRetiro}, entrega ID: {$sucursalEntrega}");

            // 4️⃣ Preparar tarifas correctamente
            $tarifaBase = $cot->tarifa_base;
            $tarifaMod = $cot->tarifa_modificada === $cot->tarifa_base ? null : $cot->tarifa_modificada;

            // 5️⃣ Subtotales SIN recalcular
            $subtotal = $cot->total / 1.16;
            $iva = $cot->total - $subtotal;

            // 6️⃣ Generar código
            $codigo = "RES-" . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            Log::info("🆕 [Convertir] Código generado: {$codigo}");

            // 7️⃣ Crear reservación principal
            $idReserva = DB::table('reservaciones')->insertGetId([
                'id_usuario'        => null,
                'id_vehiculo'       => null,
                'id_categoria'      => $cot->id_categoria,

                'ciudad_retiro'     => 1,
                'ciudad_entrega'    => 1,
                'sucursal_retiro'   => $sucursalRetiro,
                'sucursal_entrega'  => $sucursalEntrega,

                'fecha_inicio'      => $cot->pickup_date,
                'hora_retiro'       => $cot->pickup_time,
                'fecha_fin'         => $cot->dropoff_date,
                'hora_entrega'      => $cot->dropoff_time,

                'estado'            => 'pendiente_pago',

                'subtotal'          => round($subtotal, 2),
                'impuestos'         => round($iva, 2),
                'total'             => round($cot->total, 2),
                'moneda'            => 'MXN',

                'tarifa_base'       => $tarifaBase,
                'tarifa_modificada' => $tarifaMod,
                'tarifa_ajustada'   => $cot->tarifa_ajustada,

                'codigo'            => $codigo,

                'nombre_cliente'    => $cliente['nombre'] ?? null,
                'email_cliente'     => $cliente['email'] ?? null,
                'telefono_cliente'  => $cliente['telefono'] ?? null,

                'status_pago'       => 'Pendiente',
                'metodo_pago'       => 'mostrador',

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            Log::info("📝 [Convertir] Reservación creada ID {$idReserva} para cotización {$cot->folio}");

            // 8️⃣ Guardar servicios adicionales (tablas pivot)
            if (!empty($addons)) {
                foreach ($addons as $srv) {
                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion' => $idReserva,
                        'id_servicio'    => $srv['id'],
                        'cantidad'       => $srv['cantidad'],
                        'precio_unitario' => $srv['precio'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    Log::info("➕ [Convertir] Servicio añadido ID {$srv['id']} (cant={$srv['cantidad']})");
                }
            } else {
                Log::info("ℹ️ [Convertir] La cotización no tenía servicios adicionales.");
            }

            // 9️⃣ Guardar seguro (si existe)
            if (!empty($seguro) && isset($seguro['id'])) {
                DB::table('reservacion_paquete_seguro')->insert([
                    'id_reservacion' => $idReserva,
                    'id_paquete'     => $seguro['id'],
                    'precio_por_dia' => $seguro['precio'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                Log::info("🛡️ [Convertir] Paquete de seguro asignado ID {$seguro['id']}");
            } else {
                Log::info("ℹ️ [Convertir] La cotización no tenía paquete de seguro.");
            }

            // 9. Guardar servicios (Drop Off, Delivery, Gasolina)
            if (isset($servicios['dropoff']) && ($servicios['dropoff']['activo'] ?? false)) {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $idReserva,
                    'id_servicio'     => 11,
                    'cantidad'        => 1,
                    'precio_unitario' => $servicios['dropoff']['total'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            if (isset($servicios['delivery']) && ($servicios['delivery']['activo'] ?? false)) {
                DB::table('reservaciones')
                    ->where('id_reservacion', $idReserva)
                    ->update([
                        'delivery_activo'    => 1,
                        'delivery_total'     => $servicios['delivery']['total'] ?? 0,
                        'delivery_km'        => $servicios['delivery']['km'] ?? 0,
                        'delivery_direccion' => $servicios['delivery']['direccion'] ?? '',
                        'delivery_ubicacion' => $servicios['delivery']['ubicacion'] ?? null,
                        'delivery_precio_km' => $servicios['delivery']['precio_km'] ?? 0,
                    ]);
            }

            if (isset($servicios['gasolina']) && ($servicios['gasolina']['activo'] ?? false)) {
                $capacidadMax = DB::table('vehiculos')
                    ->where('id_categoria', $cot->id_categoria)
                    ->max('capacidad_tanque') ?? 0;

                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $idReserva,
                    'id_servicio'     => 1,
                    'cantidad'        => $capacidadMax,
                    'precio_unitario' => 20.00,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // 🔟 Eliminar PDF
            $pdfPath = public_path("storage/cotizaciones/{$cot->folio}.pdf");
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
                Log::info("🗑️ PDF eliminado: {$cot->folio}.pdf");
            }

            // 1️⃣1️⃣ Eliminar cotización
            DB::table('cotizaciones')->where('id_cotizacion', $id)->delete();
            Log::info("🧹 Cotización eliminada ID {$id}");

            return response()->json([
                'success' => true,
                'codigo'  => $codigo,
                'message' => 'Cotización convertida en reservación correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al convertir cotización ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al convertir cotización.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reenviarCotizacion($id)
    {
        try {
            // 1️⃣ Buscar cotización
            $cotizacion = DB::table('cotizaciones')
                ->where('id_cotizacion', $id)
                ->first();

            if (!$cotizacion) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Cotización no encontrada.'
                ], 404);
            }

            // 2️⃣ Decodificar cliente
            $cliente = json_decode($cotizacion->cliente ?? '{}', true);

            $emailCliente  = $cliente['email'] ?? null;
            $nombreCliente = trim(($cliente['nombre'] ?? 'Cliente') . ' ' . ($cliente['apellidos'] ?? ''));

            if (!$emailCliente) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ La cotización no tiene correo de cliente.'
                ], 400);
            }

            // 3️⃣ Verificar existencia del PDF
            $pdfPath = public_path("storage/cotizaciones/{$cotizacion->folio}.pdf");

            if (!file_exists($pdfPath)) {
                return response()->json([
                    'success' => false,
                    'message' => '📄 No se encontró el archivo PDF asociado a esta cotización.'
                ], 404);
            }

            // 4️⃣ Enviar correo
            $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

            Mail::to($emailCliente)
                ->cc($correoEmpresa)
                ->send(
                    new CotizacionAdminMail(
                        $nombreCliente,
                        $cotizacion->folio,
                        $pdfPath
                    )
                );

            Log::info("📨 Cotización reenviada: {$cotizacion->folio} a {$emailCliente}");

            return response()->json([
                'success' => true,
                'message' => "📨 Cotización reenviada correctamente a {$emailCliente}."
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al reenviar cotización: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '⚠️ Error interno al reenviar la cotización.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarCotizacion($id)
    {
        try {
            // 1️⃣ Buscar cotización
            $cotizacion = DB::table('cotizaciones')->where('id_cotizacion', $id)->first();

            if (!$cotizacion) {
                Log::warning("⚠️ Intento de eliminar cotización inexistente: ID {$id}");
                return response()->json([
                    'success' => false,
                    'message' => '❌ Cotización no encontrada.'
                ], 404);
            }

            // 2️⃣ Eliminar PDF si existe
            $pdfPath = public_path("storage/cotizaciones/{$cotizacion->folio}.pdf");
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
                Log::info("🗑️ PDF eliminado: {$cotizacion->folio}.pdf");
            } else {
                Log::warning("📄 No se encontró el PDF para eliminar: {$cotizacion->folio}.pdf");
            }

            // 3️⃣ Eliminar registro de la base de datos
            DB::table('cotizaciones')->where('id_cotizacion', $id)->delete();
            Log::info("✅ Cotización eliminada manualmente: {$cotizacion->folio} (ID {$id})");

            return response()->json([
                'success' => true,
                'message' => "✅ Cotización {$cotizacion->folio} eliminada correctamente."
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al eliminar cotización ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '⚠️ Error interno al eliminar cotización.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function limpiarCotizacionesVencidas()
    {
        try {
            $limite = now()->subDays(90)->toDateString();

            $cotizaciones = DB::table('cotizaciones')
                ->whereDate('dropoff_date', '<', $limite)
                ->get();

            $totalEliminadas = 0;

            foreach ($cotizaciones as $cotizacion) {
                $pdfPath = public_path("storage/cotizaciones/{$cotizacion->folio}.pdf");
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                    Log::info("🧹 [AutoClean] PDF eliminado: {$cotizacion->folio}.pdf");
                }

                DB::table('cotizaciones')->where('id_cotizacion', $cotizacion->id_cotizacion)->delete();
                $totalEliminadas++;
            }

            Log::info("🧼 Limpieza automática completada. Cotizaciones eliminadas: {$totalEliminadas}");

            return response()->json([
                'success' => true,
                'message' => "🧼 Limpieza completada. Cotizaciones eliminadas: {$totalEliminadas}"
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error en limpieza automática: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '⚠️ Error interno durante limpieza automática.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
