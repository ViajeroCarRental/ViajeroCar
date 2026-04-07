<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Mail\ReservacionAdminMail;

class ReservacionesActivasController extends Controller
{
    /**
     * 📋 Muestra todas las reservaciones activas (solo HOY y FUTURAS)
     * ✅ Excluye: cancelada, expirada, no_show
     * ✅ Soporta fecha_inicio como:
     *    - DATE/DATETIME real
     *    - string 'YYYY-MM-DD'
     *    - string 'DD/MM/YYYY'
     *
     * ✅ AGREGA:
     *    - reservaciones_anteriores (AYER) para el modal
     */
    public function index(Request $request)
    {
        try {
            $sucursal = $request->input('sucursal');
            $sucursal2 = $request->input('sucursal2');
            $perPage     = $request->input('per_page', 10);
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin    = $request->input('fecha_fin');
            $codigo   = trim((string)$request->input('codigo'));
            $search   = trim((string)$request->input('q'));

            $hoy  = Carbon::today()->format('Y-m-d');
            $ayer = Carbon::yesterday()->format('Y-m-d');

            /* ==========================================================
               ✅ RESERVACIONES ACTIVAS (HOY Y FUTURAS)
            ========================================================== */
            $reservaciones = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->leftJoin('vehiculos as v', 'r.id_vehiculo', '=', 'v.id_vehiculo')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',

                    'r.nombre_cliente',
                    'r.apellidos_cliente',
                    DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,''))) as nombre_completo"),

                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.estado',
                    'r.metodo_pago',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.hora_entrega',
                    'r.fecha_fin',
                    'r.total',
                    'r.sucursal_retiro',
                    'r.no_vuelo',
                    'r.created_at',
                    'c.codigo as categoria',
                    'c.nombre as categoria_nombre',
                    'c.descripcion as categoria_descripcion',
                    'c.precio_dia',
                    'v.transmision',
                    'v.marca as vehiculo_marca',
                    'v.modelo as vehiculo_modelo'
                )

                // ✅ solo activas
                ->whereNotIn('r.estado', ['cancelada', 'expirada', 'no_show'])


                // filtro por sucursal
                ->when($sucursal || $sucursal2, function ($q) use ($sucursal, $sucursal2) {
    $q->where(function ($sub) use ($sucursal, $sucursal2) {

        if ($sucursal) {
            $sub->where('r.sucursal_retiro', $sucursal);
        }

        if ($sucursal2) {
            $sub->orWhere('r.sucursal_retiro', $sucursal2);
        }

    });
})

//Filtro por fecha
// 🔥 Filtro por fecha (rango)
->when($fechaInicio && $fechaFin, function ($q) use ($fechaInicio, $fechaFin) {
    $q->whereBetween('r.fecha_inicio', [$fechaInicio, $fechaFin]);
})

// 🔥 Si NO hay fechas → usar hoy
->when(!($fechaInicio && $fechaFin), function ($q) use ($hoy) {
    $q->whereDate('r.fecha_inicio', '>=', $hoy);
})

                // filtro por código
                ->when($codigo, function ($q, $codigo) {
                    $q->where('r.codigo', 'LIKE', $codigo . '%');
                })

                // filtro por nombre/apellidos/correo
                ->when($search, function ($q, $search) {
                    $term = $search . '%';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('r.nombre_cliente', 'LIKE', $term)
                            ->orWhere('r.apellidos_cliente', 'LIKE', $term)
                            ->orWhere(DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,'')))"), 'LIKE', $term)
                            ->orWhere('r.email_cliente', 'LIKE', $term);
                    });
                })

                // orden
                ->orderBy('r.fecha_inicio', 'asc')
                ->orderBy('r.hora_retiro', 'asc')
                ->paginate($perPage)->withQueryString();


            $servicios = DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->select('rs.id_reservacion', 's.nombre', 'rs.cantidad')
                ->get()
                ->groupBy('id_reservacion');

            /* ==========================================================
               ✅ RESERVACIONES ANTERIORES (AYER)
               - MISMA LÓGICA (activa / filtros / orden)
               - SOLO CAMBIA EL RANGO DE FECHA A AYER
            ========================================================== */
            $reservaciones_anteriores = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',

                    'r.nombre_cliente',
                    'r.apellidos_cliente',
                    DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,''))) as nombre_completo"),

                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.estado',
                    'r.metodo_pago',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.total',
                    'r.sucursal_retiro',
                    'r.no_vuelo',
                    'c.codigo as categoria'
                )

                // ✅ solo activas (igual que la actual)
                ->whereNotIn('r.estado', ['cancelada', 'expirada', 'no_show'])

                // ✅ SOLO AYER (robusto)
                ->where(function ($q) use ($ayer) {
                    // 1) Si fecha_inicio es DATE/DATETIME real
                    $q->whereDate('r.fecha_inicio', '=', $ayer)

                    // 2) Si está como string 'YYYY-MM-DD'
                    ->orWhere('r.fecha_inicio', '=', $ayer)

                    // 3) Si está como string 'DD/MM/YYYY'
                    ->orWhereRaw(
                        "STR_TO_DATE(r.fecha_inicio, '%d/%m/%Y') = STR_TO_DATE(?, '%Y-%m-%d')",
                        [$ayer]
                    );
                })

                // filtro por sucursal
                ->when($sucursal, function ($q, $sucursal) {
                    $q->where('r.sucursal_retiro', $sucursal);
                })

                // filtro por código
                ->when($codigo, function ($q, $codigo) {
                    $q->where('r.codigo', 'LIKE', $codigo . '%');
                })

                // filtro por nombre/apellidos/correo
                ->when($search, function ($q, $search) {
                    $term = $search . '%';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('r.nombre_cliente', 'LIKE', $term)
                            ->orWhere('r.apellidos_cliente', 'LIKE', $term)
                            ->orWhere(DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,'')))"), 'LIKE', $term)
                            ->orWhere('r.email_cliente', 'LIKE', $term);
                    });
                })

                // orden
                ->orderBy('r.fecha_inicio', 'asc')
                ->orderBy('r.hora_retiro', 'asc')
                ->get();


            return view('Admin.ReservacionesActivas', [
                'reservaciones'            => $reservaciones,
                'servicios' => $servicios,
                'reservaciones_anteriores' => $reservaciones_anteriores, // ✅ YA VA AL BLADE
                'sucursalSeleccionada'      => $sucursal,
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Bookings index error: ' . $e->getMessage());
            abort(500, $e->getMessage());
        }
    }

    /**
     * 🔍 Detalles por código
     */
    public function show($codigo)
    {
        try {
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'r.id_reservacion',
                    'r.codigo',

                    'r.nombre_cliente',
                    'r.apellidos_cliente',
                    DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,''))) as nombre_completo"),

                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.estado',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.hora_entrega',
                    'r.metodo_pago',
                    'r.total',
                    'r.tarifa_modificada',
                    'r.no_vuelo',
                    DB::raw('DATEDIFF(r.fecha_fin, r.fecha_inicio) as dias'),
                    'c.codigo as categoria',
                    'c.nombre as categoria_nombre',
                    'c.descripcion as categoria_descripcion'
                )
                ->where('r.codigo', $codigo)
                ->first();

            if (!$reservacion) {
                return response()->json(['error' => 'Reservación no encontrada'], 404);
            }

            return response()->json($reservacion, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🗑️ Eliminar
     */
    public function destroy($id)
    {
        try {
            $reserv = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reserv) {
                return back()->with('error', 'La reservación no existe.');
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->delete();

            return back()->with('success', 'Reservación eliminada correctamente.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Error al eliminar la reservación: ' . $e->getMessage());
        }
    }

    /**
     * 🚫 No Show
     */
    public function noShow($id)
    {
        try {
            $reserv = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reserv) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservación no encontrada.'
                ], 404);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'estado'     => 'no_show',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservación marcada como No Show.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar No Show: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ Cancelar
     */
    public function cancelar($id)
    {
        try {
            $reserv = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reserv) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservación no encontrada.'
                ], 404);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'estado'     => 'cancelada',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservación cancelada correctamente.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✏️ Update datos + correo
     */
    public function updateDatos(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre_cliente'   => 'required|string|max:120',
                'email_cliente'    => 'required|email|max:120',
                'telefono_cliente' => 'required|string|max:40',

                'fecha_inicio'     => 'required|date',
                'hora_retiro'      => 'nullable|string|max:10',

                'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
                'hora_entrega'     => 'nullable|string|max:10',
            ]);

            $reserv = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reserv) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservación no encontrada.'
                ], 404);
            }

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'nombre_cliente'   => $validated['nombre_cliente'],
                    'email_cliente'    => $validated['email_cliente'],
                    'telefono_cliente' => $validated['telefono_cliente'],
                    'fecha_inicio'     => $validated['fecha_inicio'],
                    'hora_retiro'      => $validated['hora_retiro'],
                    'fecha_fin'        => $validated['fecha_fin'],
                    'hora_entrega'     => $validated['hora_entrega'],
                    'updated_at'       => now(),
                ]);

            $r = DB::table('reservaciones as r')
                ->leftJoin('categorias_carros as c', 'r.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'r.codigo',
                    'r.nombre_cliente',
                    'r.apellidos_cliente',
                    DB::raw("TRIM(CONCAT(COALESCE(r.nombre_cliente,''),' ',COALESCE(r.apellidos_cliente,''))) as nombre_completo"),
                    'r.email_cliente',
                    'r.telefono_cliente',
                    'r.fecha_inicio',
                    'r.hora_retiro',
                    'r.fecha_fin',
                    'r.hora_entrega',
                    'r.subtotal',
                    'r.impuestos',
                    'r.total',
                    'r.no_vuelo',
                    'r.moneda',
                    'c.nombre as categoria'
                )
                ->where('r.id_reservacion', $id)
                ->first();

            $correoCliente = trim((string) $r->email_cliente);
            $correoEmpresa = env('MAIL_FROM_ADDRESS', 'reservaciones@viajerocarental.com');
            $moneda = $r->moneda ?? 'MXN';

            if ($correoCliente && !filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
                Log::warning("⚠️ Correo inválido al guardar {$r->codigo}: {$correoCliente}");
                $correoCliente = null;
            }

            $mensaje  = "📩 CONFIRMACIÓN DE RESERVA (ACTUALIZACIÓN)\n\n";
            $mensaje .= "Código de reserva: {$r->codigo}\n";
            $mensaje .= "Categoría: " . ($r->categoria ?? '-') . "\n\n";

            $mensaje .= "👤 Cliente:\n";
            $mensaje .= "Nombre: " . ($r->nombre_completo ?: $r->nombre_cliente) . "\n";
            $mensaje .= "Correo: {$r->email_cliente}\n";
            $mensaje .= "Teléfono: {$r->telefono_cliente}\n";
            $mensaje .= "Vuelo: " . ($r->no_vuelo ?? '-') . "\n\n";

            $mensaje .= "📅 Fechas:\n";
            $mensaje .= "Entrega: {$r->fecha_inicio}" . ($r->hora_retiro ? " {$r->hora_retiro}" : "") . "\n";
            $mensaje .= "Devolución: {$r->fecha_fin}" . ($r->hora_entrega ? " {$r->hora_entrega}" : "") . "\n\n";

            $mensaje .= "💰 Montos:\n";
            $mensaje .= "Subtotal: $" . number_format($r->subtotal, 2) . " {$moneda}\n";
            $mensaje .= "Impuestos: $" . number_format($r->impuestos, 2) . " {$moneda}\n";
            $mensaje .= "Total: $" . number_format($r->total, 2) . " {$moneda}\n\n";

            $mensaje .= "📆 Enviado: " . now()->format('d/m/Y H:i:s');

            Mail::raw($mensaje, function ($msg) use ($correoCliente, $correoEmpresa, $r) {
                if ($correoCliente) {
                    $msg->to($correoCliente)
                        ->cc($correoEmpresa)
                        ->subject("Confirmación de reserva {$r->codigo} - Viajero Car Rental");
                } else {
                    $msg->to($correoEmpresa)
                        ->subject("Reserva {$r->codigo} (correo cliente inválido)");
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Datos actualizados y correo enviado correctamente.'
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Error updateDatos+correo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar y enviar correo.'
            ], 500);
        }
    }


    /**
     * ✏️ Lista de vehiculos
     */
    public function vehiculosDisponibles()
{
    $vehiculos = DB::table('vehiculos as v')
        ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
        ->leftJoin('mantenimientos as m', 'v.id_vehiculo', '=', 'm.id_vehiculo')

        ->select(
            'v.id_vehiculo',
            'v.placa',
            'c.codigo as categoria',
            'c.nombre as tamano',
            'v.modelo',
            'v.transmision',
            'v.color',

            'v.gasolina_actual',
            'v.capacidad_tanque',

            DB::raw("
            CASE
                WHEN v.capacidad_tanque > 0
                THEN ROUND((v.gasolina_actual / v.capacidad_tanque) * 16)
                ELSE 0
            END as gasolina_fraccion
            "),

            'v.kilometraje',
            'v.vigencia_verificacion',
            'm.intervalo_km',
            'v.fin_vigencia_poliza'
        )
        ->get();



    return response()->json($vehiculos);
}

public function crearContrato(Request $request)
{
    try {

        $idReservacion = $request->id_reservacion;
        $idVehiculo = $request->id_vehiculo;

        // 🔥 1. VERIFICAR SI YA EXISTE CONTRATO
        $contratoExistente = DB::table('contratos')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if ($contratoExistente) {

            // 🔥 OPCIONAL: actualizar vehículo si quieres
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update([
                    'id_vehiculo' => $idVehiculo
                ]);

            return response()->json([
                'success' => true,
                'id_contrato' => $contratoExistente->id_contrato,
                'existente' => true
            ]);
        }

        // 🔥 2. GENERAR NÚMERO DE CONTRATO
        $ultimo = DB::table('contratos')
            ->orderBy('id_contrato', 'desc')
            ->value('numero_contrato');

        $numero = $ultimo ? intval($ultimo) + 1 : 1;

        $numeroContrato = str_pad($numero, 4, "0", STR_PAD_LEFT);

        // 🔥 3. CREAR CONTRATO
        $idContrato = DB::table('contratos')->insertGetId([
            'id_reservacion' => $idReservacion,
            'numero_contrato' => $numeroContrato,
            'estado' => 'abierto',
            'abierto_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔥 4. ASIGNAR VEHÍCULO
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'id_vehiculo' => $idVehiculo
            ]);

        return response()->json([
            'success' => true,
            'id_contrato' => $idContrato,
            'existente' => false
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function reenviarCorreo($id)
{
    try {

        // 🔎 Reservación
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            return response()->json([
                'success' => false,
                'message' => 'Reservación no encontrada'
            ]);
        }

        // 🔎 Categoría
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $reservacion->id_categoria)
            ->first();

        // 🔎 Seguro
        $seguroReserva = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'sp.id_paquete', '=', 'rps.id_paquete')
            ->where('rps.id_reservacion', $id)
            ->select(
                'sp.id_paquete',
                'sp.nombre',
                'sp.descripcion',
                'rps.precio_por_dia'
            )
            ->first();

        // 🔎 Extras
        $extrasReserva = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 's.id_servicio', '=', 'rs.id_servicio')
            ->where('rs.id_reservacion', $id)
            ->select(
                's.id_servicio',
                's.nombre',
                's.descripcion',
                'rs.cantidad',
                'rs.precio_unitario',
                DB::raw('(rs.cantidad * rs.precio_unitario) as total')
            )
            ->get();

        // 🔎 Lugares
        $retiroInfo = DB::table('sucursales as s')
            ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
            ->where('s.id_sucursal', $reservacion->sucursal_retiro)
            ->select('s.nombre as sucursal', 'c.nombre as ciudad')
            ->first();

        $entregaInfo = DB::table('sucursales as s')
            ->join('ciudades as c', 'c.id_ciudad', '=', 's.id_ciudad')
            ->where('s.id_sucursal', $reservacion->sucursal_entrega)
            ->select('s.nombre as sucursal', 'c.nombre as ciudad')
            ->first();

        $lugarRetiro  = $retiroInfo ? ($retiroInfo->ciudad . ' - ' . $retiroInfo->sucursal) : '-';
        $lugarEntrega = $entregaInfo ? ($entregaInfo->ciudad . ' - ' . $entregaInfo->sucursal) : '-';

        // 🔎 Imagen (igual que el original)
        $catImages = [
            1=>'img/aveo.png',2=>'img/virtus.png',3=>'img/jetta.png',
            4=>'img/camry.png',5=>'img/renegade.png',6=>'img/taos.png',
            7=>'img/avanza.png',8=>'img/Odyssey.png',9=>'img/Hiace.png',
            10=>'img/Frontier.png',11=>'img/Tacoma.png',
        ];

        $baseUrl = rtrim(config('app.url'), '/');
        $imgPath = $catImages[$categoria->id_categoria] ?? 'img/categorias/placeholder.png';
        $imgCategoria = $baseUrl . '/' . $imgPath;

        // 🔎 Totales
        $extrasServiciosTotal = (float) $extrasReserva->sum('total');
        $dias = \Carbon\Carbon::parse($reservacion->fecha_inicio)
            ->diffInDays(\Carbon\Carbon::parse($reservacion->fecha_fin));

        $dias = max(1, $dias);

        $seguroTotal = $seguroReserva
            ? (float)$seguroReserva->precio_por_dia * $dias
            : 0;

        $opcionesRentaTotal = $extrasServiciosTotal + $seguroTotal;

        // 🔎 Tu Auto (mínimo necesario)
        $tuAuto = [
            'pax' => 5,
            'small' => 2,
            'big' => 1
        ];

        // 📧 ENVÍO
        Mail::to($reservacion->email_cliente)
            ->send(new ReservacionAdminMail(
                $reservacion,
                $categoria,
                $seguroReserva,
                $extrasReserva,
                $lugarRetiro,
                $lugarEntrega,
                $imgCategoria,
                $opcionesRentaTotal,
                $tuAuto
            ));

        return response()->json([
            'success' => true,
            'message' => 'Correo reenviado correctamente'
        ]);

    } catch (\Throwable $e) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
}

}
