<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ReservacionesAdminController extends Controller
{
    /**
     * 🧭 Vista principal de Reservaciones del administrador.
     */
    public function index()
    {
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre', 'descripcion', 'activo')
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
     * 🚘 Obtener vehículos por categoría.
     */
    public function obtenerVehiculosPorCategoria($idCategoria)
    {
        $query = DB::table('vehiculos as v')
            ->leftJoin('vehiculo_imagenes as img', 'v.id_vehiculo', '=', 'img.id_vehiculo')
            ->where('v.id_estatus', 1)
            ->select(
                'v.id_vehiculo',
                'v.nombre_publico',
                'v.marca',
                'v.modelo',
                'v.anio',
                'v.transmision',
                'v.asientos',
                'v.puertas',
                'v.precio_dia',
                'img.url as imagen'
            )
            ->groupBy(
                'v.id_vehiculo',
                'v.nombre_publico',
                'v.marca',
                'v.modelo',
                'v.anio',
                'v.transmision',
                'v.asientos',
                'v.puertas',
                'v.precio_dia',
                'img.url'
            );

        if ($idCategoria != 0) {
            $query->where('v.id_categoria', $idCategoria);
        }

        return response()->json($query->get());
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
        // 1️⃣ Validación básica
        $validated = $request->validate([
            'id_vehiculo'       => 'required|integer|exists:vehiculos,id_vehiculo',
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

        // 3️⃣ Calcular totales según el vehículo
        $vehiculo = DB::table('vehiculos')
            ->select('precio_dia', 'id_ciudad as ciudad_retiro')
            ->where('id_vehiculo', $validated['id_vehiculo'])
            ->first();

        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaFin    = Carbon::parse($validated['fecha_fin']);
        $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

        $subtotal   = $vehiculo ? ($vehiculo->precio_dia * $dias) : 0;
        $impuestos  = round($subtotal * 0.16, 2);
        $total      = $subtotal + $impuestos;
        $estado     = 'pendiente_pago';

        // 4️⃣ Insertar reservación
        $id = DB::table('reservaciones')->insertGetId([
            'id_usuario'       => null,
            'id_vehiculo'      => $validated['id_vehiculo'],
            'sucursal_retiro'  => $validated['sucursal_retiro'] ?? null,
            'sucursal_entrega' => $validated['sucursal_entrega'] ?? null,
            'ciudad_retiro'    => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'ciudad_entrega'   => $vehiculo ? $vehiculo->ciudad_retiro : 1,
            'fecha_inicio'     => $validated['fecha_inicio'],
            'hora_retiro'      => $validated['hora_retiro'],
            'fecha_fin'        => $validated['fecha_fin'],
            'hora_entrega'     => $validated['hora_entrega'],
            'estado'           => $estado,
            'subtotal'         => $subtotal,
            'impuestos'        => $impuestos,
            'total'            => $total,
            'moneda'           => 'MXN',
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

        // 5️⃣ Enviar correo (cliente + empresa)
        $correoCliente = $validated['email_cliente'] ?? null;
        $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');

        $mensaje = "📩 CONFIRMACIÓN DE RESERVA\n\n";
        $mensaje .= "Código de reserva: {$codigo}\n\n";
        $mensaje .= "👤 Cliente:\n";
        $mensaje .= "Nombre: " . ($validated['nombre_cliente'] ?? '-') . "\n";
        $mensaje .= "Correo: " . ($validated['email_cliente'] ?? '-') . "\n";
        $mensaje .= "Teléfono: " . ($validated['telefono_cliente'] ?? '-') . "\n";
        $mensaje .= "Vuelo: " . ($validated['no_vuelo'] ?? '-') . "\n\n";
        $mensaje .= "📅 Fechas:\n";
        $mensaje .= "Entrega: {$validated['fecha_inicio']} {$validated['hora_retiro']}\n";
        $mensaje .= "Devolución: {$validated['fecha_fin']} {$validated['hora_entrega']}\n\n";
        $mensaje .= "💰 Montos:\n";
        $mensaje .= "Subtotal: $" . number_format($subtotal, 2) . " MXN\n";
        $mensaje .= "Impuestos: $" . number_format($impuestos, 2) . " MXN\n";
        $mensaje .= "Total a pagar en mostrador: $" . number_format($total, 2) . " MXN\n\n";
        $mensaje .= "📆 Fecha de registro: " . now()->format('d/m/Y H:i:s') . "\n";

        try {
            Mail::raw($mensaje, function ($msg) use ($correoCliente, $correoEmpresa, $codigo) {
                if ($correoCliente) {
                    $msg->to($correoCliente)
                        ->cc($correoEmpresa)
                        ->subject("Confirmación de reserva {$codigo} - Viajero Car Rental");
                } else {
                    $msg->to($correoEmpresa)
                        ->subject("Nueva reserva {$codigo} - Viajero Car Rental");
                }
            });
        } catch (\Throwable $e) {
            Log::error("❌ Error al enviar correo de reserva: " . $e->getMessage());
        }

        // 6️⃣ Respuesta JSON (para alertify)
        return response()->json([
            'success'   => true,
            'codigo'    => $codigo,
            'id'        => $id,
            'subtotal'  => $subtotal,
            'impuestos' => $impuestos,
            'total'     => $total,
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
