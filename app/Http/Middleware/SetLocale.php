<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Si hay idioma en sesión, usarlo; si no, español por defecto
        App::setLocale(Session::get('locale', 'es'));

        return $next($request);
    }
}
