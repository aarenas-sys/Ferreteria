<?php

namespace App\Http\Middleware;

use App\Models\Caja;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CajaAbiertaMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'cajero') {
            return $next($request);
        }

        $cajaAbierta = Caja::where('usuario_id', $user->id)
            ->where('sucursal_id', $user->branch_id)
            ->where('estado', 'abierta')
            ->first();

        if (! $cajaAbierta) {
            return redirect()->route('cajero.caja.index')
                ->withErrors(['error' => 'Debe abrir caja antes de gestionar ventas, devoluciones o pagos.']);
        }

        return $next($request);
    }
}
