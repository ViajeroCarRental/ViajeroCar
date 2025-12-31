<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservacionAdminMail;

class ReservacionesAdminController extends Controller
{
    /**
     * 🧭 Vista principal de Reservaciones del administrador.
     */
    public function index()
    {
        // ✅ FIX: agregar codigo y precio_dia porque el Blade los usa
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia', 'activo')
            ->orderBy('nombre')
            ->get();

        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.activo', 1)
            ->select(
                's.id_sucursal',
                DB::raw("CONCAT(s.nombre, ' (', c.nombre, ')') as nombre_mostrado"),
                'c.id_ciudad as id_ciudad'
            )
            ->orderBy('c.nombre')
            ->get();

        return view('Admin.reservaciones', compact('categorias', 'sucursales'));
    }

    /**
     * 🚗 Obtener información de una categoría (imagen de ejemplo + tarifa base)
     */
    public function obtenerCategoriaPorId($idCategoria)
    {
        try {
            $categoria = DB::table('categorias_carros as c')
                ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
                ->leftJoin('vehiculo_imagenes as img', 'v.id_vehiculo', '=', 'img.id_vehiculo')
                ->where('c.id_categoria', $idCategoria)
                ->select(
                    'c.id_categoria',
                    'c.codigo',
                    'c.nombre',
                    'c.descripcion',
                    'c.precio_dia as tarifa_base',
                    DB::raw('COALESCE(img.url, "/assets/placeholder-car.jpg") as imagen')
                )
                ->first();

            if (!$categoria) {
                return response()->json([
                    'error' => true,
                    'message' => 'Categoría no encontrada.'
                ], 404);
            }

            return response()->json($categoria);
        } catch (\Throwable $e) {
            Log::error('❌ Error al obtener categoría: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error interno al obtener categoría.'
            ], 500);
        }
    }

    /**
     * 🛡️ Obtener paquetes de seguros activos.
     */
    public function getSeguros()
    {
        $seguros = DB::table('seguro_paquete')
            ->select('id_paquete', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
            ->where('activo', true)
            ->orderBy('precio_por_dia')
            ->get();

        return response()->json($seguros);
    }

    /**
     * 🧩 Obtener servicios adicionales activos.
     */
    public function getServicios()
    {
        $servicios = DB::table('servicios')
            ->select('id_servicio', 'nombre', 'descripcion', 'precio', 'activo')
            ->where('activo', true)
            ->orderBy('precio')
            ->get();

        return response()->json($servicios);
    }

    /**
     * 💾 Guardar nueva reservación (AJAX con Alertify)
     */
    public function guardarReservacion(Request $request)
    {
        try {
            // 🟢 0) Obtener usuario de sesión
$idUsuario = session('id_usuario');

if (!$idUsuario) {
    return response()->json([
        'success' => false,
        'message' => 'No hay usuario autenticado en el panel de administración.'
    ], 401);
}

// 🟢 0.1) Verificar que tenga rol permitido
$rolesUsuario = DB::table('usuario_rol as ur')
    ->join('roles as r', 'ur.id_rol', '=', 'r.id_rol')
    ->where('ur.id_usuario', $idUsuario)
    ->pluck('r.nombre')
    ->toArray();

// 🔁 Ajusta estos nombres según tu tabla "roles"
$rolesPermitidos = ['Rentas', 'SuperAdmin'];

$autorizado = count(array_intersect($rolesUsuario, $rolesPermitidos)) > 0;

if (!$autorizado) {
    return response()->json([
        'success' => false,
        'message' => 'No tienes permisos para crear reservaciones.'
    ], 403);
}

            // 1️⃣ Validación básica
            $validated = $request->validate([
                'id_categoria'      => 'required|integer|exists:categorias_carros,id_categoria',
                'sucursal_retiro'   => 'nullable|integer|exists:sucursales,id_sucursal',
                'sucursal_entrega'  => 'nullable|integer|exists:sucursales,id_sucursal',
                'fecha_inicio'      => 'required|date',
                'fecha_fin'         => 'required|date|after_or_equal:fecha_inicio',
                'hora_retiro'       => 'nullable|string|max:10',
                'hora_entrega'      => 'nullable|string|max:10',
                'nombre_cliente'    => 'nullable|string|max:120',
                'email_cliente'     => 'nullable|email|max:120',
                'telefono_cliente'  => 'nullable|string|max:40',
                'no_vuelo'          => 'nullable|string|max:40',
            ]);

            // 2️⃣ Generar código único
            $fecha = now()->format('Ymd');
            $random = strtoupper(Str::random(5));
            $codigo = "RES-{$fecha}-{$random}";

            // 3️⃣ Calcular totales según la categoría seleccionada
            $categoria = DB::table('categorias_carros')
                ->select('precio_dia', 'nombre', DB::raw('1 as ciudad_retiro'))
                ->where('id_categoria', $validated['id_categoria'])
                ->first();

            // ✅ SÍ: aquí seguimos usando los días de la reservación
            $tarifaBase = $request->input('precio_base_dia', $categoria->precio_dia ?? 0);

            $fechaInicio = Carbon::parse($validated['fecha_inicio']);
            $fechaFin    = Carbon::parse($validated['fecha_fin']);
            $dias        = max(1, $fechaInicio->diffInDays($fechaFin)); // 👈 días

            // Totales enviados desde frontend
            $subtotalFront  = $request->input('subtotal');
            $impuestosFront = $request->input('impuestos');
            $totalFront     = $request->input('total');

            // Si no vienen, se calculan
            if (!$subtotalFront || !$impuestosFront || !$totalFront) {
                $subtotalFront  = round($tarifaBase * $dias, 2);
                $impuestosFront = round($subtotalFront * 0.16, 2);
                $totalFront     = $subtotalFront + $impuestosFront;
            }

            $estado = 'pendiente_pago';

            // 4️⃣ Insertar reservación
            $id = DB::table('reservaciones')->insertGetId([
                'id_usuario'       => null,
                'id_categoria'     => $validated['id_categoria'],
                'sucursal_retiro'  => $validated['sucursal_retiro'] ?? null,
                'sucursal_entrega' => $validated['sucursal_entrega'] ?? null,
                'ciudad_retiro'    => $categoria ? $categoria->ciudad_retiro : 1,
                'ciudad_entrega'   => $categoria ? $categoria->ciudad_retiro : 1,
                'fecha_inicio'     => $validated['fecha_inicio'],
                'hora_retiro'      => $validated['hora_retiro'],
                'fecha_fin'        => $validated['fecha_fin'],
                'hora_entrega'     => $validated['hora_entrega'],
                'estado'           => $estado,

                // 💰 Totales
                'subtotal'         => $subtotalFront,
                'impuestos'        => $impuestosFront,
                'total'            => $totalFront,
                'moneda'           => 'MXN',

                // 🟡 Tarifa ajustada
                'tarifa_ajustada'   => $request->input('tarifa_ajustada', false),

                'tarifa_modificada' => $request->filled('tarifa_modificada')
                    ? $request->tarifa_modificada
                    : null,

                'tarifa_base'       => $tarifaBase,

                'no_vuelo'         => $validated['no_vuelo'] ?? null,
                'codigo'           => $codigo,
                'nombre_cliente'   => $validated['nombre_cliente'] ?? null,
                'email_cliente'    => $validated['email_cliente'] ?? null,
                'telefono_cliente' => $validated['telefono_cliente'] ?? null,
                'paypal_order_id'  => null,
                'status_pago'      => 'Pendiente',
                'metodo_pago'      => 'mostrador',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // 4.1️⃣ Guardar seguro seleccionado
            if ($request->filled('seguroSeleccionado.id')) {
                $seguro = $request->input('seguroSeleccionado');
                DB::table('reservacion_paquete_seguro')->insert([
                    'id_reservacion'  => $id,
                    'id_paquete'      => $seguro['id'],
                    'precio_por_dia'  => $seguro['precio'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // 4.2️⃣ Guardar servicios adicionales
            if ($request->filled('adicionalesSeleccionados')) {
                foreach ($request->input('adicionalesSeleccionados') as $extra) {
                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $extra['id'],
                        'cantidad'        => $extra['cantidad'],
                        'precio_unitario' => $extra['precio'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            // 5️⃣ Correo
            $correoCliente = $validated['email_cliente'] ?? null;
            $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            try {
                if ($correoCliente) {
                    Mail::to($correoCliente)
                        ->cc($correoEmpresa)
                        ->send(new \App\Mail\ReservacionAdminMail($reservacion, $categoria));
                } else {
                    Mail::to($correoEmpresa)
                        ->send(new \App\Mail\ReservacionAdminMail($reservacion, $categoria));
                }
            } catch (\Throwable $e) {
                Log::error("❌ Error al enviar correo de reserva: " . $e->getMessage());
            }

            return response()->json([
                'success'   => true,
                'codigo'    => $codigo,
                'id'        => $id,
                'subtotal'  => $subtotalFront,
                'impuestos' => $impuestosFront,
                'total'     => $totalFront,
                'estado'    => $estado,
                'message'   => 'Reservación creada correctamente y correo enviado.',
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Error al guardar reservación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la reservación.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
