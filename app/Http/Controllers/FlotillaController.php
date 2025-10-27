<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FlotillaController extends Controller
{
    // Mostrar autos de la flotilla
    public function indexView()
    {
        $vehiculos = DB::table('vehiculos as v')
            ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
            ->select(
                'v.id_vehiculo',
                'v.modelo',
                'v.marca',
                'v.anio',
                'v.color',
                'v.placa',
                'v.numero_serie',
                'v.kilometraje',
                'v.categoria',
                'e.nombre as estatus'
            )
            ->orderBy('v.modelo', 'asc')
            ->get();

        return view('Admin.flotilla', ['vehiculos' => $vehiculos]);
    }

    // Eliminar vehículo
    public function destroy($id)
    {
        DB::table('vehiculos')->where('id_vehiculo', $id)->delete();
        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo eliminado correctamente');
    }

    // Editar vehículo (solo ejemplo)
    public function edit(Request $request, $id)
    {
        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        if (!$vehiculo) {
            return redirect()->route('rutaFlotilla')->with('error', 'Vehículo no encontrado');
        }

        // Ejemplo de actualización rápida
        DB::table('vehiculos')->where('id_vehiculo', $id)->update([
            'color' => $request->input('color', $vehiculo->color),
            'kilometraje' => $request->input('kilometraje', $vehiculo->kilometraje),
            'updated_at' => now(),
        ]);

        return redirect()->route('rutaFlotilla')->with('success', 'Vehículo actualizado correctamente');
    }
}
