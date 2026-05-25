<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OficinaController extends Controller
{
    public function index()
    {
        try {
            // Hacemos las consultas. Si alguna tabla no existe aún, lanzará excepción.
            $sucursales = DB::table('sucursales as s')
                ->leftJoin('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
                ->select('s.*', 'c.nombre as ciudad_nombre', 'c.estado as ciudad_estado')
                ->get();

            $ciudades = DB::table('ciudades')->get();
            $categorias = DB::table('categorias_carros')->get();
            $ubicaciones = DB::table('ubicaciones_servicio')->get();
        } catch (\Exception $e) {
            // Si hay error (ej. tablas faltantes), mandamos arrays vacíos para no romper la vista
            $sucursales = collect([]);
            $ciudades = collect([]);
            $categorias = collect([]);
            $ubicaciones = collect([]);
        }

        return view('Admin.Oficinas', compact('sucursales', 'ciudades', 'categorias', 'ubicaciones'));
    }

    public function store(Request $request)
    {
        // 1. Validamos que la info venga completa y correcta
        $request->validate([
            'id_ciudad' => 'required|integer|exists:ciudades,id_ciudad',
            'nombre'    => 'required|string|max:120', // Sincronizado con el límite de la base de datos
            'direccion' => 'required|string|max:255', // Sincronizado con el límite de la base de datos
            'horario'   => 'required|string',
            'telefono'  => 'nullable|string|max:20'
        ]);

        try {
            // 2. Insertamos a la Base de Datos
            DB::table('sucursales')->insert([
                'id_ciudad' => $request->id_ciudad,
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'horario_json' => json_encode(['horario' => $request->horario]),
                'telefono' => $request->telefono,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'La sucursal "' . $request->nombre . '" fue registrada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejo de error si ya existe el nombre en esa ciudad (Unique constraint)
            if ($e->getCode() == 23000) {
                return redirect()->back()->withInput()->with('error', 'Ya existe una sucursal con ese nombre en la ciudad seleccionada.');
            }
            return redirect()->back()->withInput()->with('error', 'Error en la base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un problema al guardar: ' . $e->getMessage());
        }
    }

    public function calculate(Request $request)
    {
        try {
            $tipo = $request->tipo; // 'delivery' or 'dropoff'
            $id_categoria = $request->id_categoria;

            // Obtenemos el costo por KM de la categoría del auto
            $costo_km = DB::table('categoria_costo_km')
                ->where('id_categoria', $id_categoria)
                ->value('costo_km') ?? 0;

            if ($tipo === 'delivery') {
                $id_ubicacion = $request->id_ubicacion;

                // Obtenemos cuántos KM hay a ese destino especial
                $km = DB::table('ubicaciones_servicio')
                    ->where('id_ubicacion', $id_ubicacion)
                    ->value('km') ?? 0;

                $total = $km * $costo_km;

                return response()->json([
                    'total' => $total,
                    'km' => $km,
                    'costo_km' => $costo_km
                ]);
            } else {
                // Cálculo para Dropoff (Entre dos sucursales)
                $id_origen = $request->id_sucursal_origen;
                $id_destino = $request->id_sucursal_destino;

                // Buscamos si hay una regla específica entre estas dos sucursales
                $tarifa = DB::table('tarifa_dropoff')
                    ->where('id_sucursal_origen', $id_origen)
                    ->where('id_sucursal_destino', $id_destino)
                    ->first();

                if ($tarifa) {
                    if ($tarifa->tipo_cobro === 'fijo') {
                        $total = $tarifa->monto_base;
                    } else {
                        // Si no es fijo, se asume que multiplica la distancia configurada por el costo de la categoría
                        $total = ($tarifa->monto_por_km ?? 0) * $costo_km;
                    }
                    return response()->json([
                        'total' => $total,
                        'tipo' => $tarifa->tipo_cobro
                    ]);
                }

                // Si no se encuentra regla, devolvemos error manejado
                return response()->json([
                    'total' => 0,
                    'error' => 'No hay tarifa configurada para esta ruta de sucursales.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'total' => 0,
                'error' => 'Falta configuración en la base de datos para este cálculo.'
            ]);
        }
    }
}
