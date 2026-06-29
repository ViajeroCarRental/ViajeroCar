<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropoffController extends Controller
{
    public function index()
    {
        // 1. Categorías y su costo por km (NO se toca, funciona tal cual)
        $categorias = DB::table('categorias_carros as c')
            ->leftJoin('categoria_costo_km as ckm', 'c.id_categoria', '=', 'ckm.id_categoria')
            ->select('c.id_categoria', 'c.nombre', 'c.codigo', 'ckm.costo_km')
            ->orderBy('c.orden', 'asc')
            ->get();

        // 2. Ubicaciones (rutas) con el nombre de la ciudad de origen
        $ubicaciones = DB::table('ubicaciones_servicio as u')
            ->leftJoin('ciudades as c', 'u.id_ciudad_origen', '=', 'c.id_ciudad')
            ->select(
                'u.id_ubicacion',
                'u.id_ciudad_origen',
                'u.estado',
                'u.destino',
                'u.km',
                'u.ver_usuario',
                'u.ver_admin',
                'u.activo',
                'c.nombre as ciudad_origen_nombre',
                'c.estado as ciudad_origen_estado'
            )
            ->orderBy('c.nombre')
            ->orderBy('u.destino')
            ->get();

        // Agrupar por ciudad de origen, con Querétaro primero y el resto alfabético
        $ubicacionesPorCiudad = $ubicaciones
            ->groupBy(fn($u) => $u->ciudad_origen_nombre ?? 'Sin origen')
            ->sortBy(fn($grupo, $ciudad) => $ciudad === 'Querétaro' ? '0' : '1' . $ciudad);

        // 3. Ciudades para el select de origen del formulario
        $ciudades = DB::table('ciudades')
            ->orderBy('nombre')
            ->get();

        return view('Admin.DropoffDelivery', compact('categorias', 'ubicaciones', 'ubicacionesPorCiudad', 'ciudades'));
    }

    public function storeUbicacion(Request $request)
    {
        DB::table('ubicaciones_servicio')->insert([
            'id_ciudad_origen' => $request->id_ciudad_origen,
            'estado'           => $request->estado,
            'destino'          => $request->destino,
            'km'               => $request->km,
            'ver_usuario'      => $request->boolean('ver_usuario'),
            'ver_admin'        => $request->boolean('ver_admin'),
            'activo'           => true,
            'created_at'       => now(),
            'updated_at'       => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function updateKm(Request $request)
    {
        DB::table('ubicaciones_servicio')
            ->where('id_ubicacion', $request->id)
            ->update([
                'id_ciudad_origen' => $request->id_ciudad_origen,
                'km'               => $request->km,
                'ver_usuario'      => $request->boolean('ver_usuario'),
                'ver_admin'        => $request->boolean('ver_admin'),
                'updated_at'       => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function updateCostoKm(Request $request)
    {
        DB::table('categoria_costo_km')->updateOrInsert(
            ['id_categoria' => $request->id_categoria],
            [
                'costo_km'   => $request->costo_km,
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
}
