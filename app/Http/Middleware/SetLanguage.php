<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    public function handle(Request $request, Closure $next)
    {
        // Guardar idioma si viene en la URL
        if ($request->has('lang')) {
            session(['locale' => $request->lang]);
        }

        // Obtener idioma (por defecto español)
        $locale = session('locale', 'es');

        App::setLocale($locale);

        return $next($request);
    }
}
