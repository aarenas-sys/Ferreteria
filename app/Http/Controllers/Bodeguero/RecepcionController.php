<?php

namespace App\Http\Controllers\Bodeguero;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bodeguero\ReceiveCompraRequest;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\CompraHistorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecepcionController extends Controller
{
    public function index(): View
    {
        $branchId = Auth::user()->branch_id;

        $compras = Compra::with('proveedor')
            ->forBranch($branchId)
            ->whereIn('estado', ['pendiente', 'parcial', 'pendiente_confirmacion'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('bodeguero.recepciones.index', compact('compras'));
    }

    public function show(Compra $compra): View
    {
        $this->authorizeBranch($compra);

        $compra->load(['proveedor', 'detalles.producto']);

        return view('bodeguero.recepciones.show', compact('compra'));
    }

    public function update(ReceiveCompraRequest $request, Compra $compra): RedirectResponse
    {
        $this->authorizeBranch($compra);

        if ($compra->isRecibida()) {
            return redirect()->route('bodeguero.recepciones.index')
                ->with('error', 'Esta compra ya se encuentra completamente recibida.');
        }

        $detalles = $request->input('detalles', []);

        foreach ($detalles as $detalleData) {
            $detalle = CompraDetalle::findOrFail($detalleData['id']);

            if ($detalle->compra_id !== $compra->id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Detalle inválido para esta compra.');
            }

            $cantidadRecibida = (int) $detalleData['cantidad_recibida'];

            if ($cantidadRecibida < $detalle->cantidad_recibida) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede disminuir la cantidad recibida de un detalle.');
            }
        }

        DB::transaction(function () use ($detalles, $compra) {
            foreach ($detalles as $detalleData) {
                $detalle = CompraDetalle::findOrFail($detalleData['id']);
                $cantidadRecibida = (int) $detalleData['cantidad_recibida'];
                $cantidadAnterior = $detalle->cantidad_recibida;
                $incremento = $cantidadRecibida - $cantidadAnterior;

                // Solo incrementar stock si la compra ya está confirmada por supervisor
                if ($compra->isRecibida() && $incremento > 0) {
                    $detalle->producto->increment('stock', $incremento);
                }

                $detalle->update(['cantidad_recibida' => $cantidadRecibida]);

                CompraHistorial::create([
                    'compra_id' => $compra->id,
                    'user_id' => Auth::id(),
                    'compra_detalle_id' => $detalle->id,
                    'accion' => 'recepcion_actualizada',
                    'descripcion' => sprintf('Cantidad recibida del producto %s actualizada de %d a %d.', $detalle->producto->nombre, $cantidadAnterior, $cantidadRecibida),
                ]);
            }

            $compra->load('detalles');
            $compra->recalculateTotalReal();
            $compra->updateEstadoFromDetails();
            $compra->save();
        });

        return redirect()->route('bodeguero.recepciones.show', $compra)
            ->with('success', 'Recepción de mercancía actualizada correctamente.');
    }

    protected function authorizeBranch(Compra $compra): void
    {
        if ($compra->sucursal_id !== Auth::user()->branch_id) {
            abort(403);
        }
    }
}
