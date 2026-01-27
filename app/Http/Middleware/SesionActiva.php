<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SesionActiva
{
    /**
     * Handle an incoming request.
     *
     * Aquí ya NO medimos inactividad,
     * solo verificamos si existe id_usuario en la sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rutas donde NO queremos forzar login (para evitar bucles)
        if ($request->routeIs([
            'auth.show',
            'auth.login',
            'auth.register',
            'auth.verify',
            'auth.verify.resend',
        ])) {
            return $next($request);
        }

        // Si no hay usuario en sesión → mandar al login
        if (!session()->has('id_usuario')) {
            return redirect()
                ->route('auth.show')
                ->with(
                    'session_expired',
                    'Tu sesión ha expirado. Por favor vuelve a iniciar sesión.'
                );
        }

        // Si hay id_usuario, pasa normal
        return $next($request);
    }
}
