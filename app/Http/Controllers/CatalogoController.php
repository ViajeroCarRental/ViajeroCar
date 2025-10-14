<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    /* ======================================================
     * 📍 Catálogo inicial — carga ciudades y categorías
     * ====================================================== */
    public function index(Request $request)
    {
        // 🔹 Sucursales activas (para select de ubicación)
        $ciudades = DB::table('sucursales')
            ->select('id_sucursal', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // 🔹 Categorías de autos (para select de tipo)
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('nombre')
            ->get();

        // 🔹 En este punto no cargamos autos todavía
        $autos = collect(); // colección vacía

        return view('Usuarios.Catalogo', compact('ciudades', 'categorias', 'autos'));
    }

    /* ======================================================
     * 🔍 Filtro básico por ciudad y categoría (placeholder)
     * ====================================================== */
    public function filtrar(Request $request)
    {
        // Validar filtros
        $validated = $request->validate([
            'location' => 'nullable|string',
            'type'     => 'nullable|string',
        ]);

        // 🔹 Consultar solo los selects (sin traer autos)
        $ciudades = DB::table('sucursales')
            ->select('id_sucursal', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('nombre')
            ->get();

        // 🔹 Mensaje temporal (solo para verificar)
        $mensaje = "Filtro aplicado: "
            . ($validated['location'] ?? 'Todas las ciudades')
            . " / "
            . ($validated['type'] ?? 'Todas las categorías');

        // 🔹 No hay consulta de autos todavía
        $autos = collect();

        return view('Usuarios.Catalogo', compact('ciudades', 'categorias', 'autos', 'mensaje'));
    }

    /* ======================================================
     * ✅ Catálogo real con filtros (vehículos disponibles)
     * ====================================================== */
    public function resultados(Request $request)
    {
        // Filtros básicos
        $request->validate([
            'location' => 'nullable',
            'type'     => 'nullable',
        ]);

        $filters = [
            'location' => $request->input('location') ?: null, // id_sucursal
            'type'     => $request->input('type')     ?: null, // id_categoria
        ];

        // Listas para selects
        $ciudades = DB::table('sucursales')
            ->select('id_sucursal', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('nombre')
            ->get();

        // 🚗 Trae vehículos reales (solo disponibles)
        $autos = $this->queryVehiculos($filters);

        // Mensaje opcional
        $mensaje = 'Resultados del catálogo'
            . ($filters['location'] ? ' · Sucursal: ' . optional($ciudades->firstWhere('id_sucursal', (int)$filters['location']))->nombre : '')
            . ($filters['type']     ? ' · Categoría: ' . optional($categorias->firstWhere('id_categoria', (int)$filters['type']))->nombre : '');

        // 🧭 Si el usuario selecciona un vehículo desde el catálogo
        if ($request->filled('vehiculo_id')) {
            $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $request->vehiculo_id)->first();

            if (!$vehiculo) {
                return back()->withErrors(['vehiculo_id' => 'El vehículo seleccionado no existe o no está disponible.']);
            }

            // 🔹 Redirige al flujo de reservaciones con la información del vehículo
            return redirect()->route('reservaciones.iniciar', [
                'vehiculo_id'        => $vehiculo->id_vehiculo,
                'pickup_sucursal_id' => $vehiculo->id_sucursal,
                'dropoff_sucursal_id'=> $vehiculo->id_sucursal,
                'categoria_id'       => $vehiculo->id_categoria,
            ]);
        }

        // Si no seleccionó auto, simplemente renderiza el catálogo
        return view('Usuarios.Catalogo', compact('ciudades', 'categorias', 'autos', 'mensaje'));
    }

    /* ======================================================
     * 🔧 Helper: armar el query de vehículos (con joins)
     * ====================================================== */
    private function queryVehiculos(array $filters)
    {
        $q = DB::table('vehiculos as v')
            ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')
                  ->where('vi.orden', '=', 1);
            })
            ->join('categorias_carros as cat', 'cat.id_categoria', '=', 'v.id_categoria')
            ->leftJoin('sucursales as s', 's.id_sucursal', '=', 'v.id_sucursal')
            ->selectRaw("
                v.id_vehiculo,
                v.nombre_publico,
                v.marca,
                v.modelo,
                v.anio,
                v.transmision,
                v.asientos,
                v.puertas,
                v.precio_dia,
                v.descripcion,
                cat.nombre as categoria,
                s.nombre  as sucursal,
                COALESCE(vi.url, '') as img_url
            ")
            ->where('v.id_estatus', 1); // 1 = Disponible

        if (!empty($filters['location'])) {
            $q->where('v.id_sucursal', (int)$filters['location']);
        }

        if (!empty($filters['type'])) {
            $q->where('v.id_categoria', (int)$filters['type']);
        }

        return $q->orderBy('cat.nombre')
                 ->orderBy('v.marca')
                 ->orderBy('v.modelo')
                 ->get();
    }
}
