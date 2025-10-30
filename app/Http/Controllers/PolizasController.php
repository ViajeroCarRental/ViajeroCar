<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PolizasController extends Controller
{
    /**
     * 📄 Mostrar la vista de pólizas con datos desde la base de datos
     */
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
            ->whereNotNull('v.no_poliza')
            ->orderBy('v.fin_vigencia_poliza', 'asc')
            ->get();

        return view('Admin.polizas', compact('polizas'));
    }

    /**
     * 📥 Descargar archivo de póliza
     */
    public function descargar($archivo)
    {
        // ✅ Ruta relativa dentro de storage/app/public/
        $path = 'polizas/' . $archivo;

        // ✅ Verificar si el archivo existe en el disco 'public'
        if (Storage::disk('public')->exists($path)) {
            // 🔽 Descargar el archivo (sin errores ni advertencias)
            return Storage::disk('public')->download($path, $archivo);
        }

        // ⚠️ Si no existe, mostrar error amigable
        return redirect()->back()->with('error', 'El archivo no existe en el servidor.');
    }
    public function editar($id)
{
    $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
    return view('Admin.editarPoliza', compact('vehiculo'));
}

public function actualizar(Request $request, $id)
{
    DB::table('vehiculos')->where('id_vehiculo', $id)->update([
        'no_poliza' => $request->no_poliza,
        'aseguradora' => $request->aseguradora,
        'inicio_vigencia_poliza' => $request->inicio_vigencia_poliza,
        'fin_vigencia_poliza' => $request->fin_vigencia_poliza,
        'plan_seguro' => $request->plan_seguro,
    ]);

    return redirect()->route('rutaPolizas')->with('success', 'Póliza actualizada correctamente.');
}

public function subirArchivo($id)
{
    $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();
    return view('Admin.subirArchivoPoliza', compact('vehiculo'));
}

public function guardarArchivo(Request $request, $id)
{
    $request->validate([
        'archivo_poliza' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
    ]);

    $file = $request->file('archivo_poliza');
    $nombreArchivo = uniqid() . '.' . $file->getClientOriginalExtension();
    $file->storeAs('public/polizas', $nombreArchivo);

    DB::table('vehiculos')->where('id_vehiculo', $id)->update([
        'archivo_poliza' => $nombreArchivo
    ]);

    return redirect()->route('rutaPolizas')->with('success', 'Archivo actualizado correctamente.');
}
}
