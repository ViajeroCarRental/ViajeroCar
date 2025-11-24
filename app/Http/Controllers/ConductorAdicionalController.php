<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConductorAdicionalController extends Controller
{
    /* ============================================================
       📌 MOSTRAR ANEXO (vista del documento)
    ============================================================ */
    public function verAnexo($id)
    {
        // Buscar reservación real
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $id)
            ->first();

        // Si no existe → modo DEMO
        if (!$reservacion) {
            $reservacion = (object)[
                'id_reservacion'    => $id,
                'nombre_cliente'    => 'Cliente Demo',
                'email_cliente'     => 'demo@email.com',
                'telefono_cliente'  => '0000000000',
                'firma_arrendador'  => null,   // ← necesario para evitar error
            ];
        } else {
            // Si existe pero falta la propiedad (por selects manuales)
            if (!property_exists($reservacion, 'firma_arrendador')) {
                $reservacion->firma_arrendador = null;
            }
        }

        // Conductores extra
        $conductores = DB::table('conductores_adicionales')
            ->where('id_reservacion', $id)
            ->get();

        return view('Admin.anexo-conductores', compact('reservacion', 'conductores'));
    }



    /* ============================================================
       📌 GUARDAR CONDUCTOR ADICIONAL
    ============================================================ */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_reservacion'   => 'required|integer|exists:reservaciones,id_reservacion',
            'nombre'           => 'required|string|max:150',
            'edad'             => 'nullable|integer',
            'licencia'         => 'required|string',
            'vence'            => 'nullable|string',
            'imagen_licencia'  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // Procesar imagen si viene
        $rutaImagen = null;

        if ($request->hasFile('imagen_licencia')) {
            $archivo = $request->file('imagen_licencia');

            $nombreArchivo = time() . '_' . preg_replace('/\s+/', '_', $archivo->getClientOriginalName());
            $archivo->move(public_path('conductores'), $nombreArchivo);

            $rutaImagen = 'conductores/' . $nombreArchivo;
        }

        // Guardar en DB
        DB::table('conductores_adicionales')->insert([
            'id_reservacion'  => $request->id_reservacion,
            'nombre'          => $request->nombre,
            'edad'            => $request->edad,
            'licencia'        => $request->licencia,
            'vence'           => $request->vence,
            'imagen_licencia' => $rutaImagen,
            'firmado'         => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return back()->with('ok', 'Conductor agregado correctamente.');
    }



    /* ============================================================
       📌 ELIMINAR CONDUCTOR
    ============================================================ */
    public function eliminar($id)
    {
        DB::table('conductores_adicionales')
            ->where('id_conductor', $id)
            ->delete();

        return back()->with('ok', 'Conductor eliminado.');
    }



    /* ============================================================
       📌 GUARDAR FIRMA DEL ARRENDADOR (canvas)
       Lo puedes usar en tu JS para guardar la firma digital
    ============================================================ */
    public function guardarFirma(Request $request)
    {
        $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'firma'          => 'required|string', // base64
        ]);

        // Guardar firma como imagen PNG
        $imagen = $request->firma;
        $imagen = str_replace('data:image/png;base64,', '', $imagen);
        $imagen = str_replace(' ', '+', $imagen);

        $nombre = 'firma_' . $request->id_reservacion . '_' . time() . '.png';
        $ruta = public_path('firmas/' . $nombre);

        if (!file_exists(public_path('firmas'))) {
            mkdir(public_path('firmas'), 0777, true);
        }

        file_put_contents($ruta, base64_decode($imagen));

        // Actualizar reservación
        DB::table('reservaciones')
            ->where('id_reservacion', $request->id_reservacion)
            ->update([
                'firma_arrendador' => 'firmas/' . $nombre
            ]);

        return response()->json(['ok' => true, 'ruta' => 'firmas/' . $nombre]);
    }
}
