<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisorReservacionController extends Controller
{


//   MOSTRAR
public function mostrarReservacion($id)
{
    $reservacion = DB::table('reservaciones')
        ->where('id_reservacion', $id)
        ->first();

    if (!$reservacion)
    {
        abort(404, 'Reservación no encontradaaa');
    }

    $servicios = DB::table('reservacion_servicio')
        ->join('servicios','reservacion_servicio.id_servicio','=','servicios.id_servicio')
        ->where('reservacion_servicio.id_reservacion',$id)
        ->select(
            'servicios.id_servicio',
            'servicios.nombre',
            'reservacion_servicio.cantidad',
            'reservacion_servicio.precio_unitario'
        )
        ->get();

    $subtotalServicios = 0;

    foreach ($servicios as $s) {
        $subtotalServicios += $s->cantidad * $s->precio_unitario;
    }

    $li = 350000;
    $subtotal = $subtotalServicios + $li;
    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    $reservacionVista = DB::table('reservaciones as r')
        ->leftJoin('ciudades as cr','r.ciudad_retiro','=','cr.id_ciudad')
        ->leftJoin('sucursales as sr','r.sucursal_retiro','=','sr.id_sucursal')
        ->leftJoin('ciudades as ce','r.ciudad_entrega','=','ce.id_ciudad')
        ->leftJoin('sucursales as se','r.sucursal_entrega','=','se.id_sucursal')
        ->where('r.id_reservacion',$id)
        ->select(
            'r.*',
            DB::raw("CONCAT(sr.nombre,' (',cr.nombre,')') as pickupName"),
            DB::raw("CONCAT(se.nombre,' (',ce.nombre,')') as dropoffName")
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

    return view('Usuarios.visorReservacion', [
        'reservacion' => $reservacionVista,
        'servicios'   => $servicios,
        'subtotal'    => $subtotal,
        'iva'         => $iva,
        'total'       => $total,
        'sucursales'  => $sucursales,
    ]);
}


public function actualizarReservacion(Request $request, $id)
{
    DB::beginTransaction();

    try {

        DB::table('reservaciones')
        ->where('id_reservacion',$id)
        ->update([

            'nombre_cliente'    => $request->nombre_cliente,
            'apellidos_cliente' => $request->apellidos_cliente,
            'email_cliente'     => $request->email_cliente,
            'telefono_cliente'  => $request->telefono_cliente,

            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,

            'hora_retiro'  => $request->hora_retiro,
            'hora_entrega' => $request->hora_entrega,

            'sucursal_retiro'   => $request->sucursal_retiro,
            'sucursal_entrega'  => $request->sucursal_entrega,

             //Categoria.
            'id_categoria'      => $request->id_categoria,

            'updated_at'   => now()
        ]);

        //Elimina los servicios coexistentes.
        DB::table('reservacion_servicio')
            ->where('id_reservacion',$id)
            ->delete();

        $subtotalServicios = 0;

        if($request->servicios){
            foreach($request->servicios as $s){
                if($s['cantidad'] > 0){
                    DB::table('reservacion_servicio')->insert([
                        'id_reservacion'  => $id,
                        'id_servicio'     => $s['id'],
                        'cantidad'        => $s['cantidad'],
                        'precio_unitario' => $s['precio'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $subtotalServicios +=
                        $s['cantidad'] * $s['precio'];
                }

            }

        }

        $li = 350000;
        $subtotal = $subtotalServicios + $li;
        $iva   = $subtotal * 0.16;
        $total = $subtotal + $iva;

        DB::table('reservaciones')
        ->where('id_reservacion',$id)
        ->update([

            'subtotal'  => $subtotal,
            'impuestos' => $iva,
            'total'     => $total

        ]);


        DB::commit();

        return back()->with('success','Reservación actualizada');


    } catch(\Exception $e){

        DB::rollBack();

        return back()->with('error','Error al actualizar');

    }
}

public function eliminarReservacion($id)
{
    DB::beginTransaction();

    try{

        DB::table('reservacion_servicio')
            ->where('id_reservacion',$id)
            ->delete();


        DB::table('reservaciones')
            ->where('id_reservacion',$id)
            ->delete();


        DB::commit();

        return redirect('/ventas')
            ->with('success','Reservación eliminada');


    }catch(\Exception $e){

        DB::rollBack();

        return back()
            ->with('error','No se pudo eliminar');
    }
}

}


