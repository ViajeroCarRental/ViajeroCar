<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PolizasController extends Controller
{
    /** 📋 Listar todas las pólizas */
    public function index()
    {
        $polizas = DB::table('vehiculos as v')
            ->leftJoin('estatus_carro as e', 'v.id_estatus', '=', 'e.id_estatus')
            ->select(
                'v.id_vehiculo',
                'v.nombre_publico',
                'v.placa',
                'v.no_poliza',
                'v.aseguradora',
                'v.plan_seguro',
                'v.inicio_vigencia_poliza',
                'v.fin_vigencia_poliza',
                'v.archivo_poliza',
                'e.nombre as estatus'
            )
            ->orderBy('v.fin_vigencia_poliza', 'asc')
            ->get();

        return view('Admin.polizas', compact('polizas'));
    }

    /** ✏️ Actualizar datos de póliza */
    public function actualizar(Request $request, $id)
    {
        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update([
                'no_poliza'              => $request->no_poliza,
                'aseguradora'            => $request->aseguradora,
                'plan_seguro'            => $request->plan_seguro,
                'inicio_vigencia_poliza' => $request->inicio_vigencia_poliza,
                'fin_vigencia_poliza'    => $request->fin_vigencia_poliza,
            ]);

        return redirect()->route('rutaPolizas')->with('success', 'Póliza actualizada correctamente.');
    }

    /** 📤 Subir o reemplazar archivo */
    public function guardarArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo_poliza' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096'
        ]);

        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
        if (!$vehiculo) {
            return back()->with('error', 'Vehículo no encontrado.');
        }

        $file   = $request->file('archivo_poliza');
        $nombre = 'poliza_' . Str::random(12) . '.' . $file->getClientOriginalExtension();

        // 📁 Guardar archivo en storage/app/public/polizas
        Storage::disk('public')->putFileAs('polizas', $file, $nombre);

        // 🗑️ Eliminar archivo anterior si existe
        if (!empty($vehiculo->archivo_poliza)) {
            $rutasPosibles = [
                'polizas/' . $vehiculo->archivo_poliza,
                'uploads/' . $vehiculo->archivo_poliza,
                $vehiculo->archivo_poliza
            ];
            foreach ($rutasPosibles as $ruta) {
                if (Storage::disk('public')->exists($ruta)) {
                    Storage::disk('public')->delete($ruta);
                    break;
                }
            }
        }

        // 💾 Actualizar BD
        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update(['archivo_poliza' => $nombre]);

        return redirect()->route('rutaPolizas')->with('success', 'Archivo subido o reemplazado correctamente.');
    }

    /** 👁️ Ver archivo sin depender del symlink */
    public function ver($id)
    {
        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        if (!$vehiculo || !$vehiculo->archivo_poliza) {
            abort(404, 'Archivo no registrado.');
        }

        $nombre = $vehiculo->archivo_poliza;

        // 🔍 Buscar en múltiples posibles rutas
        $rutas = [
            'polizas/' . $nombre,
            'uploads/' . $nombre,
            $nombre,
        ];

        foreach ($rutas as $ruta) {
            if (Storage::disk('public')->exists($ruta)) {
                return Storage::disk('public')->response($ruta);
            }
        }

        abort(404, 'Archivo no encontrado en el servidor.');
    }

    /** ⬇️ Descargar archivo */
    public function descargar($id)
{
    $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
    if (!$vehiculo || !$vehiculo->archivo_poliza) {
        return back()->with('error', 'Archivo no disponible.');
    }

    // 🧹 Limpiar el nombre para evitar caracteres inválidos
    $nombreLimpio = basename($vehiculo->archivo_poliza);

    $rutas = [
        'polizas/' . $nombreLimpio,
        'uploads/' . $nombreLimpio,
        $nombreLimpio,
    ];

    foreach ($rutas as $ruta) {
        if (Storage::disk('public')->exists($ruta)) {
            return Storage::disk('public')->download($ruta, $nombreLimpio);
        }
    }

    return back()->with('error', 'El archivo no existe en el servidor.');
}

}
