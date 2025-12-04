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
            'v.nombre_publico',
            'v.placa',
            's.fecha',
            's.tipo',
            's.deducible',
            's.descripcion',  // 🟢 agregar este campo
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
        'folio'       => 'nullable|string|max:50|unique:siniestros,folio',
        'fecha'       => 'required|date',
        'tipo'        => 'required|string',
        'estatus'     => 'nullable|string|max:50',
        'deducible'   => 'nullable|numeric|min:0',
        'descripcion' => 'nullable|string|max:1000',
    ]);

    // 🔐 Generar folio si no viene
    $folio = $request->folio;
    if (!$folio) {
        $folio = 'SIN-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
        while (DB::table('siniestros')->where('folio', $folio)->exists()) {
            $folio = 'SIN-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
        }
    }

// 🆕 Insertar siniestro
DB::table('siniestros')->insert([
    'id_vehiculo' => $request->id_vehiculo,
    'folio'       => $folio,
    'fecha'       => $request->fecha,
    'tipo'        => $request->tipo,
    'estatus'     => $request->estatus ?? 'Abierto',
    'deducible'   => $request->deducible,
    'rin'         => null,
    'descripcion' => $request->descripcion,
    'created_at'  => now(),
    'updated_at'  => now(),
]);

// 💰 Registrar gasto asociado al siniestro (sin cambiar de vista)
DB::table('gastos')->insert([
    'id_vehiculo' => $request->id_vehiculo,
    'tipo'        => 'Siniestro',
    'descripcion' => $request->descripcion ?? 'Gasto por siniestro del vehículo',
    'monto'       => $request->deducible ?? 0,
    'fecha'       => $request->fecha,
    'created_at'  => now(),
    'updated_at'  => now(),
]);

return redirect()->route('rutaSeguros')->with('success', 'Siniestro y gasto registrado correctamente.');

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

        return redirect()->route('rutaSeguros')->with('success', 'Siniestro actualizado correctamente.');
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

        return redirect()->route('rutaSeguros')->with('success', 'Archivo subido correctamente.');

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
    public function buscarVehiculos(Request $request)
{
    $q = trim($request->get('q', ''));

    if ($q === '') {
        return response()->json([]);
    }

    $vehiculos = DB::table('vehiculos')
        ->select(
            'id_vehiculo',
            'nombre_publico',
            'placa',
            'color',
            'numero_serie',
            'anio'
        )
        ->where(function ($w) use ($q) {
            $w->where('placa', 'like', "%{$q}%")
              ->orWhere('color', 'like', "%{$q}%")
              ->orWhere('numero_serie', 'like', "%{$q}%")
              ->orWhere('anio', 'like', "%{$q}%")
              ->orWhere('nombre_publico', 'like', "%{$q}%");
        })
        ->orderBy('anio', 'desc')
        ->limit(12)
        ->get();

    // Formato amigable para el frontend
    $data = $vehiculos->map(function ($v) {
        $label = "{$v->nombre_publico} {$v->anio}";
        $det   = [];
        if ($v->placa) $det[] = "Placa: {$v->placa}";
        if ($v->color) $det[] = "Color: {$v->color}";
        if ($v->numero_serie) $det[] = "Serie: {$v->numero_serie}";
        $sub   = implode(' · ', $det);

        return [
            'id'    => $v->id_vehiculo,
            'label' => $label,
            'sub'   => $sub,
        ];
    });

    return response()->json($data);
}
}
