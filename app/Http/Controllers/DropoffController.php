<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropoffController extends Controller
{
    // public function index()
    // {
    //     return view('Admin.DropoffDelivery');
    // }

    public function index()
    {
        // 1. Traemos las categorías y su costo por km (Hacemos un join por si están en tablas separadas)
        $categorias = DB::table('categorias_carros as c')
            ->leftJoin('categoria_costo_km as ckm', 'c.id_categoria', '=', 'ckm.id_categoria')
            ->select('c.id_categoria', 'c.nombre', 'c.codigo', 'ckm.costo_km')
            ->orderBy('c.orden', 'asc')
            ->get();

        // 2. Traemos las ubicaciones de Delivery
        $ubicaciones = DB::table('ubicaciones_servicio')->get();

        // 3. Traemos las tarifas de Dropoff (Uniendo con las sucursales para tener los nombres)
        $tarifas = DB::table('tarifa_dropoff as t')
            ->join('sucursales as o', 't.id_sucursal_origen', '=', 'o.id_sucursal')
            ->join('sucursales as d', 't.id_sucursal_destino', '=', 'd.id_sucursal')
            ->select('t.*', 'o.nombre as origen_nombre', 'd.nombre as destino_nombre')
            ->get();

        // 4. Traemos las sucursales para llenar los <select> del modal de nuevo Dropoff
        $sucursales = DB::table('sucursales')->where('activo', 1)->get();

        // Mandamos TODO a la vista
        return view('Admin.DropoffDelivery', compact('categorias', 'ubicaciones', 'tarifas', 'sucursales'));
    }

    public function data()
    {
        $categorias = DB::table('categorias_carros as c')
            ->leftJoin('categoria_costo_km as ck', 'c.id_categoria', '=', 'ck.id_categoria')
            ->select(
                'c.id_categoria',
                'c.codigo',
                'c.nombre',
                'c.activo',
                DB::raw('IFNULL(ck.costo_km, 0) as costo_km')
            )
            ->get();

        $ubicaciones = DB::table('ubicaciones_servicio')->get();

        $tarifas_dropoff = DB::table('tarifa_dropoff as t')
            ->leftJoin('ciudades as co', 't.id_ciudad_origen', '=', 'co.id_ciudad')
            ->leftJoin('sucursales as so', 't.id_sucursal_origen', '=', 'so.id_sucursal')
            ->leftJoin('ciudades as cd', 't.id_ciudad_destino', '=', 'cd.id_ciudad')
            ->leftJoin('sucursales as sd', 't.id_sucursal_destino', '=', 'sd.id_sucursal')
            ->select(
                't.*',
                'co.nombre as ciudad_origen',
                'so.nombre as sucursal_origen',
                'cd.nombre as ciudad_destino',
                'sd.nombre as sucursal_destino'
            )
            ->get();

        $ciudades = DB::table('ciudades')->select('id_ciudad', 'nombre')->get();
        $sucursales = DB::table('sucursales')->select('id_sucursal', 'nombre', 'id_ciudad')->get();

        return response()->json([
            'categorias' => $categorias,
            'ubicaciones' => $ubicaciones,
            'tarifas_dropoff' => $tarifas_dropoff,
            'ciudades' => $ciudades,
            'sucursales' => $sucursales
        ]);
    }

    public function storeUbicacion(Request $request)
    {
        DB::table('ubicaciones_servicio')->insert([
            'estado' => $request->estado,
            'destino' => $request->destino,
            'km' => $request->km,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function storeTarifaDropoff(Request $request)
    {
        DB::table('tarifa_dropoff')->insert([
            'id_ciudad_origen' => $request->id_ciudad_origen,
            'id_sucursal_origen' => $request->id_sucursal_origen,
            'id_ciudad_destino' => $request->id_ciudad_destino,
            'id_sucursal_destino' => $request->id_sucursal_destino,
            'tipo_cobro' => $request->tipo_cobro ?? 'fijo',
            'monto_base' => $request->monto_base ?? 0,
            'monto_por_km' => $request->monto_por_km ?? 0,
            'moneda' => 'MXN',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function updateKm(Request $request)
    {
        DB::table('ubicaciones_servicio')
            ->where('id_ubicacion', $request->id)
            ->update([
                'km' => $request->km,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function updateCostoKm(Request $request)
    {
        DB::table('categoria_costo_km')->updateOrInsert(
            ['id_categoria' => $request->id_categoria],
            [
                'costo_km' => $request->costo_km,
                'updated_at' => now()
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroyUbicacion($id)
    {
        DB::table('ubicaciones_servicio')->where('id_ubicacion', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function destroyTarifaDropoff($id)
    {
        DB::table('tarifa_dropoff')->where('id_tarifa', $id)->delete();
        return response()->json(['success' => true]);
    }
}
