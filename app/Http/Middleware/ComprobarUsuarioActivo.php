<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el admin desactiva a un usuario con la sesión abierta,
 * se le cierra la sesión en su siguiente petición.
 */
class ComprobarUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->activo) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
