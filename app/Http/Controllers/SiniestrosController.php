<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SiniestrosController extends Controller
{
    /** 📋 Listar todos los siniestros */
    public function index()
    {
        $siniestros = DB::table('siniestros as s')
            ->join('vehiculos as v', 's.id_vehiculo', '=', 'v.id_vehiculo')
            ->select(
                's.id_siniestro',
                's.folio',
                'v.nombre_publico',
                'v.placa',
                's.fecha',
                's.tipo',
                's.estatus',
                's.deducible',
                's.rin',
                's.archivo'
            )
            ->orderBy('s.fecha', 'desc')
            ->get();

        return view('Admin.seguros', compact('siniestros'));
    }

    /** 🆕 Registrar nuevo siniestro */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_vehiculo' => 'required|exists:vehiculos,id_vehiculo',
            'folio' => 'required|string|max:50|unique:siniestros',
            'fecha' => 'required|date',
            'tipo' => 'required|string',
            'estatus' => 'nullable|string|max:50',
            'deducible' => 'nullable|numeric|min:0',
            'rin' => 'nullable|string|max:100',
        ]);

        DB::table('siniestros')->insert([
            'id_vehiculo' => $request->id_vehiculo,
            'folio' => $request->folio,
            'fecha' => $request->fecha,
            'tipo' => $request->tipo,
            'estatus' => $request->estatus ?? 'Abierto',
            'deducible' => $request->deducible,
            'rin' => $request->rin,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('rutaSiniestros')->with('success', 'Siniestro registrado correctamente.');
    }

    /** ✏️ Editar siniestro */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'folio' => 'required|string|max:50',
            'fecha' => 'required|date',
            'tipo' => 'required|string',
            'estatus' => 'required|string|max:50',
            'deducible' => 'nullable|numeric|min:0',
            'rin' => 'nullable|string|max:100',
        ]);

        DB::table('siniestros')->where('id_siniestro', $id)->update([
            'folio' => $request->folio,
            'fecha' => $request->fecha,
            'tipo' => $request->tipo,
            'estatus' => $request->estatus,
            'deducible' => $request->deducible,
            'rin' => $request->rin,
            'updated_at' => now(),
        ]);

        return redirect()->route('rutaSiniestros')->with('success', 'Siniestro actualizado correctamente.');
    }

    /** 📤 Subir archivo (PDF o imagen) */
    public function subirArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $siniestro = DB::table('siniestros')->where('id_siniestro', $id)->first();
        if (!$siniestro) {
            return back()->with('error', 'Siniestro no encontrado.');
        }

        $file = $request->file('archivo');
        $nombre = 'siniestro_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('siniestros', $file, $nombre);

        // Eliminar archivo anterior si existía
        if (!empty($siniestro->archivo) && Storage::disk('public')->exists('siniestros/' . $siniestro->archivo)) {
            Storage::disk('public')->delete('siniestros/' . $siniestro->archivo);
        }

        DB::table('siniestros')->where('id_siniestro', $id)->update(['archivo' => $nombre]);

        return redirect()->route('rutaSiniestros')->with('success', 'Archivo subido correctamente.');
    }

    /** 👁️ Ver archivo */
    public function ver($id)
    {
        $siniestro = DB::table('siniestros')->where('id_siniestro', $id)->first();
        if (!$siniestro || !$siniestro->archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $ruta = 'siniestros/' . $siniestro->archivo;
        if (Storage::disk('public')->exists($ruta)) {
            return Storage::disk('public')->response($ruta);
        }

        abort(404, 'Archivo no disponible en el servidor.');
    }

    /** ⬇️ Descargar archivo */
    public function descargar($id)
    {
        $siniestro = DB::table('siniestros')->where('id_siniestro', $id)->first();
        if (!$siniestro || !$siniestro->archivo) {
            return back()->with('error', 'Archivo no disponible.');
        }

        $ruta = 'siniestros/' . $siniestro->archivo;
        if (Storage::disk('public')->exists($ruta)) {
            return Storage::disk('public')->download($ruta, basename($siniestro->archivo));
        }

        return back()->with('error', 'Archivo no encontrado en el servidor.');
    }
}
