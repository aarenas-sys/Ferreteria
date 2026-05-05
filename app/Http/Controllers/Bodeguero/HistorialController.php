<?php

namespace App\Http\Controllers\Bodeguero;

use App\Http\Controllers\Controller;
use App\Models\CompraHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistorialController extends Controller
{
    public function index(): View
    {
        $historiales = CompraHistorial::with(['compra', 'detalle.producto'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('bodeguero.historial', compact('historiales'));
    }
}
