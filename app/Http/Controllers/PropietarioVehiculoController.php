<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropietarioVehiculoController extends Controller
{

    // ===============================
    // MOSTRAR VISTA
    // ===============================
    public function index()
    {
        return view('admin.propietarios');
    }

    // ===============================
    // LISTAR
    // ===============================
    public function list()
    {
        $vehiculos = DB::table('vehiculos')
            ->select(
                'id_vehiculo',
                'marca',
                'modelo',
                'placa',
                'propietario as nombre_propietario',
                'firma_propietario'
            )
            ->orderBy('marca')
            ->get();

        return response()->json($vehiculos);
    }

    // ===============================
    // MOSTRAR UNO
    // ===============================
    public function show($id)
    {
        $vehiculo = DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->select(
                'id_vehiculo',
                'propietario as nombre_propietario',
                'firma_propietario'
            )
            ->first();

        return response()->json($vehiculo);
    }

    // ===============================
    // CREAR
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'id_vehiculo' => 'required|integer',
            'nombre_propietario' => 'required|string|max:120',
            'firma_propietario' => 'required|string'
        ]);

        $nombre = trim($request->nombre_propietario);
    $firma  = $request->firma_propietario;

    // 1) Guardar en el vehículo seleccionado
    DB::table('vehiculos')
        ->where('id_vehiculo', $request->id_vehiculo)
        ->update([
            'propietario'       => $nombre,
            'firma_propietario' => $firma,
            'updated_at'        => now()
        ]);

    // 2) Propagar la MISMA firma a todos los vehículos con el mismo propietario
    DB::table('vehiculos')
        ->where('propietario', $nombre)
        ->update([
            'firma_propietario' => $firma,
            'updated_at'        => now()
        ]);

    return response()->json(['success' => true]);
}
    // ===============================
    // ACTUALIZAR
    // ===============================
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_propietario' => 'required|string|max:120',
            'firma_propietario' => 'required|string'
        ]);

     $nombre = trim($request->nombre_propietario);
        $firma  = $request->firma_propietario;

    // Nombre anterior (por si cambió el propietario en este vehículo)
    $anterior = DB::table('vehiculos')
        ->where('id_vehiculo', $id)
        ->value('propietario');

    // 1) Actualizar el vehículo editado
    DB::table('vehiculos')
        ->where('id_vehiculo', $id)
        ->update([
            'propietario'       => $nombre,
            'firma_propietario' => $firma,
            'updated_at'        => now()
        ]);

    // 2) Propagar la firma a todos los vehículos con el MISMO propietario (nuevo)
    DB::table('vehiculos')
        ->where('propietario', $nombre)
        ->update([
            'firma_propietario' => $firma,
            'updated_at'        => now()
        ]);

    return response()->json(['success' => true]);
}

// ===============================
// BUSCAR FIRMA POR NOMBRE (para autocompletar al registrar)
// ===============================
public function buscarPorNombre(Request $request)
{
    $nombre = trim($request->query('nombre', ''));

    if ($nombre === '') {
        return response()->json(['firma' => null]);
    }

    $firma = DB::table('vehiculos')
        ->where('propietario', $nombre)
        ->whereNotNull('firma_propietario')
        ->value('firma_propietario');

    return response()->json(['firma' => $firma]);
}




    // ===============================
    // ELIMINAR
    // ===============================
    public function destroy($id)
    {
        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update([
                'propietario' => null,
                'firma_propietario' => null,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
}
