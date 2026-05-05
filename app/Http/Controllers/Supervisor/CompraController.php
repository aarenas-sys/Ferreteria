<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\StoreCompraRequest;
use App\Models\Compra;
use App\Models\CompraHistorial;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = Auth::user()->branch_id;
        $search = $request->get('search');

        $compras = Compra::with(['proveedor', 'usuario'])
            ->forBranch($branchId)
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhere('tipo_pago', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhereHas('proveedor', function ($providerQuery) use ($search) {
                            $providerQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('supervisor.compras.index', compact('compras', 'search'));
    }

    public function create(): View
    {
        $branchId = Auth::user()->branch_id;

        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::where('sucursal_id', $branchId)
            ->orderBy('nombre')
            ->get();

        return view('supervisor.compras.create', compact('proveedores', 'productos'));
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        $branchId = Auth::user()->branch_id;

        DB::transaction(function () use ($request, $branchId) {
            $data = $request->validated();
            $detalles = $data['detalles'];

            $compra = Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => Auth::id(),
                'sucursal_id' => $branchId,
                'tipo_pago' => $data['tipo_pago'],
                'estado' => 'pendiente',
                'total_estimado' => collect($detalles)->sum(fn ($item) => $item['cantidad_solicitada'] * $item['precio_compra']),
                'total_real' => 0,
            ]);

            foreach ($detalles as $detalle) {
                $compra->detalles()->create([
                    'producto_id' => $detalle['producto_id'],
                    'cantidad_solicitada' => $detalle['cantidad_solicitada'],
                    'cantidad_recibida' => 0,
                    'precio_compra' => $detalle['precio_compra'],
                    'subtotal' => $detalle['cantidad_solicitada'] * $detalle['precio_compra'],
                ]);
            }
        });

        return redirect()->route('supervisor.compras.index')
            ->with('success', 'Compra registrada correctamente y en estado pendiente.');
    }

    public function show(Compra $compra): View
    {
        $this->authorizeBranch($compra);

        $compra->load(['proveedor', 'usuario', 'detalles.producto']);

        return view('supervisor.compras.show', compact('compra'));
    }

    public function confirmarRecepcion(Compra $compra): RedirectResponse
    {
        $this->authorizeBranch($compra);

        if (!$compra->isPendienteConfirmacion()) {
            return redirect()->route('supervisor.compras.show', $compra)
                ->with('error', 'Esta compra no está pendiente de confirmación.');
        }

        DB::transaction(function () use ($compra) {
            // Incrementar stock de todos los productos recibidos
            foreach ($compra->detalles as $detalle) {
                if ($detalle->cantidad_recibida > 0) {
                    $detalle->producto->increment('stock', $detalle->cantidad_recibida);
                }
            }

            $compra->estado = 'recibida';
            $compra->fecha_recepcion = now();
            $compra->save();

            CompraHistorial::create([
                'compra_id' => $compra->id,
                'user_id' => Auth::id(),
                'accion' => 'confirmar_recepcion',
                'descripcion' => 'Recepción de mercancía confirmada por supervisor. Stock actualizado.',
            ]);
        });

        return redirect()->route('supervisor.compras.show', $compra)
            ->with('success', 'Recepción de mercancía confirmada correctamente.');
    }

    public function destroy(Compra $compra): RedirectResponse
    {
        $this->authorizeBranch($compra);

        if (!$compra->isPendiente()) {
            return redirect()->route('supervisor.compras.show', $compra)
                ->with('error', 'Solo se pueden eliminar compras en estado pendiente.');
        }

        $compra->delete();

        return redirect()->route('supervisor.compras.index')
            ->with('success', 'Compra pendiente eliminada correctamente.');
    }

    protected function authorizeBranch(Compra $compra): void
    {
        if ($compra->sucursal_id !== Auth::user()->branch_id) {
            abort(403);
        }
    }
}
