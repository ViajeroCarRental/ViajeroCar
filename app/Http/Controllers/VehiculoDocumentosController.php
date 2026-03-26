<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehiculoDocumentosController extends Controller
{
    public function index()
    {
        return view('admin.vehiculos-documentos');
    }

 public function list()
{
    try {

        $data = DB::table('vehiculos')
            ->select(
                'id_vehiculo',
                'marca',
                'modelo',

                DB::raw('archivo_cartafactura IS NOT NULL as archivo_cartafactura'),
                DB::raw('archivo_poliza IS NOT NULL as archivo_poliza'),
                DB::raw('archivo_verificacion IS NOT NULL as archivo_verificacion'),
                DB::raw('archivo_tarjetacirculacion IS NOT NULL as archivo_tarjetacirculacion')
            )
            ->orderBy('marca')
            ->get();

        return response()->json($data);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);

    }
}

    public function getArchivo($id, $tipo)
    {
        $permitidos = [
            'archivo_cartafactura',
            'archivo_poliza',
            'archivo_verificacion',
            'archivo_tarjetacirculacion'
        ];

        if (!in_array($tipo, $permitidos)) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        if (!$vehiculo || !$vehiculo->$tipo) {
            return response()->json(['error' => 'No existe'], 404);
        }

        $file = $vehiculo->$tipo;

        $mime = finfo_buffer(finfo_open(), $file, FILEINFO_MIME_TYPE);

        return response($file)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline');
    }

    public function store(Request $request, $id, $tipo)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        $file = file_get_contents($request->file('archivo'));

        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update([
                $tipo => $file,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id, $tipo)
    {
        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update([
                $tipo => null,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
}
