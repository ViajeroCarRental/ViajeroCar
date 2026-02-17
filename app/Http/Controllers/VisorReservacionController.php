<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisorReservacionController extends Controller
{
     /* =========================================================
       GET ÚNICO – MUESTRA LAS 3 CARDS
    ========================================================= */

    public function mostrarReservacion($id)
    {
        // ---------- CARD 1 ----------
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select('id_reservacion', 'id_categoria')
            ->first();

        if (!$reservacion) {
            abort(404, 'Reservación no encontrada');
        }

        // Servicios asociados
        $servicios = DB::table('reservacion_servicio')
            ->join('servicios', 'reservacion_servicio.id_servicio', '=', 'servicios.id_servicio')
            ->where('reservacion_servicio.id_reservacion', $id)
            ->select(
                'servicios.id_servicio',
                'servicios.nombre',
                'reservacion_servicio.cantidad',
                'reservacion_servicio.precio_unitario'
            )
            ->get();

        // Totales
        $subtotalServicios = 0;
        foreach ($servicios as $s) {
            $subtotalServicios += $s->cantidad * $s->precio_unitario;
        }

        $li = 350000;
        $subtotal = $subtotalServicios + $li;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;

        // Catálogo de servicios
        $catalogoServicios = DB::table('servicios')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        // ---------- CARD 2 ----------
        $cliente = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select(
                'nombre_cliente',
                'apellidos_cliente',
                'email_cliente',
                'telefono_cliente'
            )
            ->first();

        // ---------- CARD 3 ----------
        $itinerario = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->select(
                'fecha_inicio',
                'fecha_fin',
                'hora_retiro',
                'hora_entrega',
                'sucursal_retiro',
                'sucursal_entrega'
            )
            ->first();

        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->where('s.activo', 1)
            ->select(
                's.id_sucursal',
                DB::raw("CONCAT(s.nombre,' (',c.nombre,')') as nombre_mostrado")
            )
            ->orderBy('c.nombre')
            ->get();


            // ---------- CONTRATO ----------
            $tieneContrato = DB::table('contratos')
            ->where('id_reservacion', $id)
            ->exists();


        return view('Usuarios.visorReservacion', [
            // Card 1
            'reservacion'        => $reservacion,
            'servicios'          => $servicios,
            'subtotal'           => $subtotal,
            'iva'                => $iva,
            'total'              => $total,
            'catalogoServicios' => $catalogoServicios,

            // Card 2
            'cliente'            => $cliente,

            // Card 3
            'itinerario'         => $itinerario,
            'sucursales'         => $sucursales,

            'tieneContrato'      => $tieneContrato,
        ]);
    }

    /* =========================================================
       PUT ÚNICO – DECIDE QUÉ CARD ACTUALIZAR
    ========================================================= */

    public function actualizarReservacion(Request $request, $id)
    {

         // 🔒 BLOQUEO POR CONTRATO
    $existeContrato = DB::table('contratos')
        ->where('id_reservacion', $id)
        ->exists();

    if ($existeContrato) {
        return back()->with('error', 'No se puede editar la reservación porque ya tiene contrato');
    }

        switch ($request->card) {
            case 'card1':
                return $this->actualizarCard1($request, $id);
            case 'card2':
                return $this->actualizarCard2($request, $id);
            case 'card3':
                return $this->actualizarCard3($request, $id);
            default:
                return back()->with('error', 'Acción no válida');
        }
    }

    private function actualizarCard1(Request $request, $id)
    {
        $request->validate([
            'id_categoria'         => 'required|integer',
            'servicios'            => 'required|array',
            'servicios.*.id'       => 'required|integer',
            'servicios.*.cantidad' => 'required|integer|min:1',
            'servicios.*.precio'   => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Categoría
            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'id_categoria' => $request->id_categoria,
                    'updated_at'   => now()
                ]);

            // Servicios
            DB::table('reservacion_servicio')
                ->where('id_reservacion', $id)
                ->delete();

            $subtotalServicios = 0;

            foreach ($request->servicios as $s) {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $id,
                    'id_servicio'     => $s['id'],
                    'cantidad'        => $s['cantidad'],
                    'precio_unitario' => $s['precio'],
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);

                $subtotalServicios += $s['cantidad'] * $s['precio'];
            }

            // Totales
            $li = 350000;
            $subtotal = $subtotalServicios + $li;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->update([
                    'subtotal'  => $subtotal,
                    'impuestos' => $iva,
                    'total'     => $total
                ]);

            DB::commit();
            return back()->with('success', 'Vehículo y servicios actualizados');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar Card 1');
        }
    }

    /* =========================================================
       CARD 2 – DATOS DEL CLIENTE
    ========================================================= */

    private function actualizarCard2(Request $request, $id)
    {
        $request->validate([
            'nombre_cliente'    => 'required|string|max:100',
            'apellidos_cliente' => 'required|string|max:100',
            'email_cliente'     => 'required|email',
            'telefono_cliente'  => 'required|string|max:20'
        ]);

        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'nombre_cliente'    => $request->nombre_cliente,
                'apellidos_cliente' => $request->apellidos_cliente,
                'email_cliente'     => $request->email_cliente,
                'telefono_cliente'  => $request->telefono_cliente,
                'updated_at'        => now()
            ]);

        return back()->with('success', 'Datos del cliente actualizados');
    }

    /* =========================================================
       CARD 3 – FECHAS, HORAS Y SUCURSALES
    ========================================================= */

    private function actualizarCard3(Request $request, $id)
    {
        $request->validate([
            'fecha_inicio'     => 'required|date',
            'fecha_fin'        => 'required|date|after_or_equal:fecha_inicio',
            'hora_retiro'      => 'required',
            'hora_entrega'     => 'required',
            'sucursal_retiro'  => 'required|integer',
            'sucursal_entrega' => 'required|integer'
        ]);

        // Validación de horas si la fecha es la misma
        if ($request->fecha_inicio === $request->fecha_fin) {
            if ($request->hora_entrega <= $request->hora_retiro) {
                return back()->withErrors([
                    'hora_entrega' =>
                        'La hora de entrega debe ser posterior a la de retiro'
                ]);
            }
        }

        DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->update([
                'fecha_inicio'     => $request->fecha_inicio,
                'fecha_fin'        => $request->fecha_fin,
                'hora_retiro'      => $request->hora_retiro,
                'hora_entrega'     => $request->hora_entrega,
                'sucursal_retiro'  => $request->sucursal_retiro,
                'sucursal_entrega' => $request->sucursal_entrega,
                'updated_at'       => now()
            ]);

        return back()->with('success', 'Fechas y sucursales actualizadas');
    }
}
