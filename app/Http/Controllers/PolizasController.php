<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

    /** ✏️ Actualizar póliza y registrar gasto acumulado */
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

        // 🧾 Registrar costo en tabla gastos
        if ($request->filled('costo_poliza') && $request->costo_poliza > 0) {
            $costo = (float) $request->costo_poliza;
            $fecha = Carbon::now();

            // Buscar gasto previo (póliza o poliza)
            $gastoExistente = DB::table('gastos')
                ->where('id_vehiculo', $id)
                ->whereIn('tipo', ['póliza', 'poliza'])
                ->first();

            if ($gastoExistente) {
                // 🔁 Acumular monto
                DB::table('gastos')
                    ->where('id_gasto', $gastoExistente->id_gasto)
                    ->update([
                        'monto' => $gastoExistente->monto + $costo,
                        'updated_at' => $fecha,
                    ]);
            } else {
                // 🆕 Crear nuevo gasto
                DB::table('gastos')->insert([
                    'id_vehiculo' => $id,
                    'tipo'        => 'póliza',
                    'descripcion' => 'Costo de póliza de seguro',
                    'monto'       => $costo,
                    'fecha'       => $fecha,
                    'created_at'  => $fecha,
                    'updated_at'  => $fecha,
                ]);
            }
        }

        return redirect()->route('rutaPolizas')
            ->with('success', 'Póliza y gasto actualizados correctamente.');
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

        // 📁 Guardar en storage
        Storage::disk('public')->putFileAs('polizas', $file, $nombre);

        // 🗑️ Eliminar archivo anterior si existía
        if (!empty($vehiculo->archivo_poliza)) {
            $rutas = ['polizas/' . $vehiculo->archivo_poliza, 'uploads/' . $vehiculo->archivo_poliza];
            foreach ($rutas as $ruta) {
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

        return redirect()->route('rutaPolizas')
            ->with('success', 'Archivo subido o reemplazado correctamente.');
    }

    /** 👁️ Ver archivo */
    public function ver($id)
    {
        $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $id)->first();

        if (!$vehiculo || !$vehiculo->archivo_poliza) {
            abort(404, 'Archivo no registrado.');
        }

        $nombre = basename($vehiculo->archivo_poliza);
        $rutas = ['polizas/' . $nombre, 'uploads/' . $nombre, $nombre];

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

        $nombre = basename($vehiculo->archivo_poliza);
        $rutas = ['polizas/' . $nombre, 'uploads/' . $nombre, $nombre];

        foreach ($rutas as $ruta) {
            if (Storage::disk('public')->exists($ruta)) {
                // ⚙️ BOM UTF-8 para nombres con acentos
                return Storage::disk('public')->download($ruta, $nombre);
            }
        }

        return back()->with('error', 'El archivo no existe en el servidor.');
    }
}
