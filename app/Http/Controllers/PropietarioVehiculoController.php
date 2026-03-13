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

        DB::table('vehiculos')
            ->where('id_vehiculo', $request->id_vehiculo)
            ->update([
                'propietario' => $request->nombre_propietario,
                'firma_propietario' => $request->firma_propietario,
                'updated_at' => now()
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

        DB::table('vehiculos')
            ->where('id_vehiculo', $id)
            ->update([
                'propietario' => $request->nombre_propietario,
                'firma_propietario' => $request->firma_propietario,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
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
