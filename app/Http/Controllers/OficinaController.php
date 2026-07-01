<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OficinaController extends Controller
{
    public function index()
    {
        try {
            $sucursales = DB::table('sucursales as s')
                ->leftJoin('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
                ->select(
                    's.id_sucursal',
                    's.id_ciudad',
                    's.nombre',
                    's.direccion',
                    's.telefono',
                    's.horario_json',
                    's.url_direccion',
                    's.ver_usuario',
                    's.ver_admin',
                    's.activo',
                    'c.nombre as ciudad_nombre',
                    'c.estado as ciudad_estado',
                    DB::raw('(s.imagen_1 IS NOT NULL) as tiene_imagen_1'),
                    DB::raw('(s.imagen_2 IS NOT NULL) as tiene_imagen_2')
                )
                ->orderBy('c.nombre')
                ->orderBy('s.nombre')
                ->get();

            $sucursalesPorCiudad = $sucursales
                ->groupBy('ciudad_nombre')
                ->sortBy(fn($grupo, $ciudad) => $ciudad === 'Querétaro' ? '0' : '1' . $ciudad);

            $ciudades = DB::table('ciudades')
                ->orderBy('nombre')
                ->get();
        } catch (\Exception $e) {
            $sucursalesPorCiudad = collect([]);
            $ciudades = collect([]);
        }

        return view('Admin.Oficinas', compact('sucursalesPorCiudad', 'ciudades'));
    }

    public function imagen($id, $num)
    {
        $columna = $num == 2 ? 'imagen_2' : 'imagen_1';

        $row = DB::table('sucursales')
            ->where('id_sucursal', $id)
            ->select($columna . ' as contenido')
            ->first();

        if (!$row || !$row->contenido) {
            abort(404);
        }

        $bytes = $row->contenido;
        $mime = 'image/jpeg';
        if (substr($bytes, 0, 8) === "\x89PNG\r\n\x1a\n") {
            $mime = 'image/png';
        } elseif (substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            $mime = 'image/jpeg';
        } elseif (substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            $mime = 'image/webp';
        }

        return response($bytes, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ciudad'     => 'required|integer|exists:ciudades,id_ciudad',
            'nombre'        => 'required|string|max:120',
            'direccion'     => 'nullable|string|max:255',
            'horario'       => 'required|string',
            'telefono'      => 'nullable|string|max:20',
            'url_direccion' => 'nullable|string|max:500',
            'imagen_1'      => 'nullable|string',
            'imagen_2'      => 'nullable|string',
        ]);

        try {
            DB::table('sucursales')->insert([
                'id_ciudad'     => $request->id_ciudad,
                'nombre'        => mb_strtoupper($request->nombre, 'UTF-8'),
                'direccion'     => mb_strtoupper($request->direccion, 'UTF-8'),
                'horario_json'  => json_encode(['horario' => $request->horario]),
                'telefono'      => $request->telefono,
                'url_direccion' => $request->url_direccion,
                'imagen_1'      => $this->decodeImagen($request->imagen_1),
                'imagen_2'      => $this->decodeImagen($request->imagen_2),
                'ver_usuario'   => $request->boolean('ver_usuario'),
                'ver_admin'     => $request->boolean('ver_admin'),
                'activo'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Limpia el caché del home para que la nueva sucursal se vea de inmediato
            \App\Http\Controllers\BusquedaController::limpiarCacheCiudades();

            return redirect()->back()->with('success', 'La sucursal "' . $request->nombre . '" fue registrada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()->withInput()->with('error', 'Ya existe una sucursal con ese nombre en la ciudad seleccionada.');
            }
            return redirect()->back()->withInput()->with('error', 'Error en la base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un problema al guardar: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_ciudad'     => 'required|integer|exists:ciudades,id_ciudad',
            'nombre'        => 'required|string|max:120',
            'direccion'     => 'nullable|string|max:255',
            'horario'       => 'required|string',
            'telefono'      => 'nullable|string|max:20',
            'url_direccion' => 'nullable|string|max:500',
            'imagen_1'      => 'nullable|string',
            'imagen_2'      => 'nullable|string',
        ]);

        try {
            $datos = [
                'id_ciudad'     => $request->id_ciudad,
                'nombre'        => mb_strtoupper($request->nombre, 'UTF-8'),
                'direccion'     => mb_strtoupper($request->direccion, 'UTF-8'),
                'horario_json'  => json_encode(['horario' => $request->horario]),
                'telefono'      => $request->telefono,
                'url_direccion' => $request->url_direccion,
                'ver_usuario'   => $request->boolean('ver_usuario'),
                'ver_admin'     => $request->boolean('ver_admin'),
                'updated_at'    => now(),
            ];

            if ($request->filled('imagen_1')) {
                $datos['imagen_1'] = $this->decodeImagen($request->imagen_1);
            }
            if ($request->filled('imagen_2')) {
                $datos['imagen_2'] = $this->decodeImagen($request->imagen_2);
            }

            DB::table('sucursales')
                ->where('id_sucursal', $id)
                ->update($datos);

            // Limpia el caché del home para que los cambios se vean de inmediato
            \App\Http\Controllers\BusquedaController::limpiarCacheCiudades();

            return redirect()->back()->with('success', 'La sucursal "' . $request->nombre . '" fue actualizada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Ya existe una sucursal con ese nombre en la ciudad seleccionada.');
            }
            return redirect()->back()->with('error', 'Error en la base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un problema al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('sucursales')->where('id_sucursal', $id)->delete();

            // Limpia el caché del home para que la eliminación se refleje de inmediato
            \App\Http\Controllers\BusquedaController::limpiarCacheCiudades();

            return redirect()->back()->with('success', 'Sucursal eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'No se puede eliminar: la sucursal tiene registros vinculados.');
            }
            return redirect()->back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un problema al eliminar: ' . $e->getMessage());
        }
    }

    private function decodeImagen($dataUrl)
    {
        if (!$dataUrl || !is_string($dataUrl)) {
            return null;
        }

        if (str_contains($dataUrl, ',')) {
            $dataUrl = explode(',', $dataUrl, 2)[1];
        }

        $bytes = base64_decode($dataUrl, true);

        return $bytes === false ? null : $bytes;
    }
}
