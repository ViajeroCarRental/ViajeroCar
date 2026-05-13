<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CatalogoController extends Controller
{
    /**
     * Duración del caché de categorías.
     * 24 horas porque las categorías cambian máximo 1-2 veces al año.
     * Si editas algo en la BD, ejecuta limpiarCacheCategorias() para
     * forzar refresh inmediato.
     */
    private const CACHE_TTL = 60 * 60 * 24; // 24 horas (en segundos)
    private const CACHE_KEY_ALL = 'catalogo.categorias.all';

    /**
     * /catalogo
     * Punto de entrada principal, delega a resultados().
     */
    public function index(Request $request)
    {
        return $this->resultados($request);
    }

    /**
     * /catalogo/resultados
     * Muestra el catálogo con las cards de categorías.
     * Soporta filtro opcional por id_categoria (?type=X).
     */
    public function resultados(Request $request)
    {
        // Validación: type es opcional pero si llega debe ser entero positivo
        $request->validate([
            'type' => 'nullable|integer|min:1',
        ]);

        $type = $request->input('type');

        // Cargar todas las categorías desde caché (o de la BD la primera vez).
        // Como las categorías son fijas, una sola query cada 24h es suficiente
        // para todos los usuarios del sitio.
        $todasLasCategorias = Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return DB::table('categorias_carros')
                ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
                ->orderBy('nombre')
                ->get();
        });

        // Si llega filtro ?type=X, lo aplicamos en memoria (sin volver a la BD)
        $categoriasCards = !empty($type)
            ? $todasLasCategorias->where('id_categoria', $type)->values()
            : $todasLasCategorias;

        return view('Usuarios.Catalogo', [
            'categoriasCards' => $categoriasCards,
        ]);
    }

    /**
     * Limpia el caché de categorías.
     * Úsalo desde Tinker después de editar precios/descripciones en la BD:
     *
     *   php artisan tinker
     *   >>> app(\App\Http\Controllers\CatalogoController::class)->limpiarCacheCategorias();
     *
     * O desde un seeder/comando si quieres automatizarlo.
     */
    public function limpiarCacheCategorias(): bool
    {
        return Cache::forget(self::CACHE_KEY_ALL);
    }
}
