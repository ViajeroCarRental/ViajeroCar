<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    // /catalogo → usa el mismo catálogo con resultados
    public function index(Request $request)
    {
        return $this->resultados($request);
    }

    // /catalogo/resultados
    public function resultados(Request $request)
    {
        $request->validate([
            'type' => 'nullable'
        ]);

        $type = $request->input('type');

        // 🔹 Categorías para el select
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
            ->orderBy('nombre')
            ->get();

        // 🔹 Categorías que se van a mostrar como "cards"
        $queryCards = DB::table('categorias_carros')
            ->select('id_categoria', 'codigo', 'nombre', 'descripcion', 'precio_dia')
            ->orderBy('nombre');

        if (!empty($type)) {
            $queryCards->where('id_categoria', $type);
        }

        $categoriasCards = $queryCards->get();

        return view('Usuarios.Catalogo', [
            'categorias'      => $categorias,      // para el select
            'categoriasCards' => $categoriasCards, // para las cards
        ]);
    }
}
