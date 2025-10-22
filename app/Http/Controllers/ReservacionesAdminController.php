<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
     * 💾 Guardar nueva reservación enviada desde el flujo JS.
     */
    public function guardarReservacion(Request $request)
    {
        try {
            // 🧩 Validación de campos
            $request->validate([
                'id_vehiculo'       => 'required|integer',
                'sucursal_retiro'   => 'required|integer',
                'sucursal_entrega'  => 'required|integer',
                'fecha_inicio'      => 'required|date',
                'fecha_fin'         => 'required|date|after_or_equal:fecha_inicio',
                'hora_retiro'       => 'nullable|string|max:10',
                'hora_entrega'      => 'nullable|string|max:10',
                'subtotal'          => 'required|numeric',
                'impuestos'         => 'required|numeric',
                'total'             => 'required|numeric',
                'moneda'            => 'required|string|max:5',
                'nombre_cliente'    => 'required|string|max:100',
                'email_cliente'     => 'required|email|max:100',
                'telefono_cliente'  => 'required|string|max:30',
                'no_vuelo'          => 'nullable|string|max:40',
            ]);

            // 🏙️ Obtener ciudades desde las sucursales
            $ciudadRetiro = DB::table('sucursales')->where('id_sucursal', $request->sucursal_retiro)->value('id_ciudad');
            $ciudadEntrega = DB::table('sucursales')->where('id_sucursal', $request->sucursal_entrega)->value('id_ciudad');

            if (!$ciudadRetiro || !$ciudadEntrega) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la ciudad de retiro o entrega.'
                ], 400);
            }

            // 🚘 Validar que el vehículo exista
            $vehiculoExiste = DB::table('vehiculos')->where('id_vehiculo', $request->id_vehiculo)->exists();
            if (!$vehiculoExiste) {
                return response()->json([
                    'success' => false,
                    'message' => 'El vehículo seleccionado no existe o no está disponible.'
                ], 400);
            }

            // 🔹 Generar código único
            $codigo = 'R-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

            // 💾 Insertar reservación
            DB::table('reservaciones')->insert([
                'id_usuario'       => null,
                'id_vehiculo'      => $request->id_vehiculo,
                'sucursal_retiro'  => $request->sucursal_retiro,
                'sucursal_entrega' => $request->sucursal_entrega,
                'ciudad_retiro'    => $ciudadRetiro,
                'ciudad_entrega'   => $ciudadEntrega,
                'fecha_inicio'     => $request->fecha_inicio,
                'hora_retiro'      => $request->hora_retiro,
                'fecha_fin'        => $request->fecha_fin,
                'hora_entrega'     => $request->hora_entrega,
                'estado'           => 'pendiente_pago',
                'hold_expires_at'  => now()->addHours(24),
                'subtotal'         => $request->subtotal,
                'impuestos'        => $request->impuestos,
                'total'            => $request->total,
                'moneda'           => $request->moneda,
                'no_vuelo'         => $request->no_vuelo,
                'codigo'           => $codigo,
                'nombre_cliente'   => $request->nombre_cliente,
                'email_cliente'    => $request->email_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'paypal_order_id'  => null,
                'status_pago'      => 'Pendiente',
                'metodo_pago'      => 'mostrador',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return response()->json([
                'success' => true,
                'codigo'  => $codigo,
                'message' => 'Reservación registrada correctamente con estado pendiente de pago.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la reservación: ' . $e->getMessage(),
            ], 500);
        }
    }
}
