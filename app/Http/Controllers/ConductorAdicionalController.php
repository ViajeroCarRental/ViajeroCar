<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConductorAdicionalController extends Controller
{
    // 📌 VER ANEXO
    public function verAnexo($id)
    {
                    $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $id)
                ->first();

            if (!$reservacion) {
                // 🔥 Modo demo para poder visualizar el documento
                $reservacion = (object)[
                    'id_reservacion' => $id,
                    'nombre_cliente' => 'Cliente Demo',
                    'email_cliente' => 'demo@email.com',
                    'telefono_cliente' => '0000000000'
                ];
            }


        $conductores = DB::table('conductores_adicionales')
            ->where('id_reservacion', $id)
            ->get();

        return view('Admin.anexo-conductores', compact('reservacion', 'conductores'));
    }


    // 📌 GUARDAR CONDUCTOR
  public function guardar(Request $request)
{
    $request->validate([
        'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
        'nombre'         => 'required|string|max:150',
        'edad'           => 'nullable|integer',
        'licencia'       => 'required|string',
        'vence'          => 'nullable|string',
        'imagen_licencia' => 'nullable|image|mimes:jpg,jpeg,png|max:4096'
    ]);

    // Procesar imagen
    $rutaImagen = null;

    if ($request->hasFile('imagen_licencia')) {
        $archivo = $request->file('imagen_licencia');
        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $archivo->move(public_path('conductores'), $nombreArchivo);

        $rutaImagen = 'conductores/' . $nombreArchivo;
    }

    DB::table('conductores_adicionales')->insert([
        'id_reservacion' => $request->id_reservacion,
        'nombre'         => $request->nombre,
        'edad'           => $request->edad,
        'licencia'       => $request->licencia,
        'vence'          => $request->vence,
        'imagen_licencia'=> $rutaImagen,
        'firmado'        => false,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return back()->with('ok', 'Conductor agregado correctamente.');
}


    // 📌 ELIMINAR CONDUCTOR
    public function eliminar($id)
    {
        DB::table('conductores_adicionales')
            ->where('id_conductor', $id)
            ->delete();

        return back()->with('ok', 'Conductor eliminado.');
    }
}
