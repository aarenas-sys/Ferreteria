<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Tiempo de inactividad permitido en minutos (debe coincidir con SESSION_LIFETIME)
     */
    private const INACTIVITY_TIMEOUT = 2;

    /**
     * Rutas que NO deben actualizar last_activity
     */
    private const EXCLUDED_PATHS = [
        'favicon.ico',
        '.well-known',
        'artisan',
        '/logout',
        '/session/ping',
        '/js/',
        '/css/',
        '/images/',
        '.png',
        '.jpg',
        '.css',
        '.js',
        '.woff',
        '.map',
    ];

    /**
     * Manejo de la solicitud
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir logout sin verificación de timeout
        if ($request->getPathInfo() === '/logout' || $request->getPathInfo() === '/session/ping') {
            return $next($request);
        }

        // Solo verificar para usuarios autenticados
        if (Auth::check()) {
            $lastActivityKey = 'last_activity';
            $lastActivity = Session::get($lastActivityKey);
            $currentTime = time();
            $path = $request->getPathInfo();

            // Verificar timeout primero (antes de actualizar actividad)
            if ($lastActivity) {
                $timeoutInSeconds = self::INACTIVITY_TIMEOUT * 60;
                $elapsedTime = $currentTime - $lastActivity;

                // Si pasó el tiempo de inactividad, cerrar sesión
                if ($elapsedTime > $timeoutInSeconds) {
                    Log::info('Session timeout - User logout', [
                        'user_id' => Auth::id(),
                        'elapsed_seconds' => $elapsedTime,
                        'timeout_seconds' => $timeoutInSeconds,
                        'path' => $path,
                    ]);

                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();

                    return redirect('/login')
                        ->with('warning', 'Tu sesión expiró por inactividad. Por favor, vuelve a iniciar sesión.');
                }
            }

            // Actualizar last_activity SOLO en rutas "reales" (no assets ni favicons)
            if (!$this->isExcludedPath($path)) {
                Session::put($lastActivityKey, $currentTime);
                Log::debug('Session activity updated', [
                    'user_id' => Auth::id(),
                    'path' => $path,
                    'time' => date('Y-m-d H:i:s', $currentTime),
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Verificar si la ruta debe ser excluida de actualización de actividad
     */
    private function isExcludedPath(string $path): bool
    {
        foreach (self::EXCLUDED_PATHS as $excludedPath) {
            if (str_contains(strtolower($path), strtolower($excludedPath))) {
                return true;
            }
        }
        return false;
    }
}

