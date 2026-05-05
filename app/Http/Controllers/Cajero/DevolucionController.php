<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cajero\StoreDevolucionRequest;
use App\Models\Caja;
use App\Models\Devolucion;
use App\Models\DevolucionDetalle;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\CajaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DevolucionController extends Controller
{
    public function create(Venta $venta)
    {
        if ($venta->sucursal_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para procesar devoluciones en esta venta.');
        }

        if ($venta->tipo_venta === 'credito') {
            abort(403, 'No se permiten devoluciones en ventas a crédito.');
        }

        if (now()->diffInDays($venta->fecha_venta) > 15) {
            abort(403, 'La venta supera los 15 días permitidos para devolución.');
        }

        $venta->load(['detalles.producto', 'cliente', 'usuario', 'devoluciones.detalles.producto']);

        $returnedQuantities = DevolucionDetalle::whereHas('devolucion', function ($query) use ($venta) {
            $query->where('venta_id', $venta->id);
        })
            ->select('producto_id')
            ->selectRaw('SUM(cantidad) as total_devuelta')
            ->groupBy('producto_id')
            ->pluck('total_devuelta', 'producto_id')
            ->toArray();

        $remaining = $venta->detalles->sum(function ($detalle) use ($returnedQuantities) {
            return max(0, $detalle->cantidad - ($returnedQuantities[$detalle->producto_id] ?? 0));
        });

        if ($remaining <= 0) {
            return redirect()->route('cajero.ventas.index')
                ->with('error', 'Esta venta ya se devolvió completamente.');
        }

        return view('cajero.devoluciones.create', compact('venta', 'returnedQuantities'));
    }

    public function index()
    {
        $devoluciones = Devolucion::whereHas('venta', function ($query) {
            $query->where('sucursal_id', auth()->user()->branch_id);
        })
            ->with(['venta.cliente', 'usuario'])
            ->orderBy('fecha_devolucion', 'desc')
            ->paginate(15);

        return view('cajero.devoluciones.index', compact('devoluciones'));
    }

    public function store(StoreDevolucionRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $venta = Venta::with('detalles.producto')->findOrFail($request->venta_id);

            if ($venta->sucursal_id !== auth()->user()->branch_id) {
                abort(403, 'No tienes permiso para procesar devoluciones en esta venta.');
            }

            if ($venta->tipo_venta === 'credito') {
                throw ValidationException::withMessages([
                    'venta' => 'No se permiten devoluciones en ventas a crédito.',
                ]);
            }

            if (now()->diffInDays($venta->fecha_venta) > 15) {
                throw ValidationException::withMessages([
                    'venta' => 'La venta supera los 15 días permitidos para devolución.',
                ]);
            }

            // Obtener caja activa del cajero
            $cajaService = new CajaService();
            $caja = $cajaService->obtenerCajaAbierta();

            if (!$caja) {
                throw ValidationException::withMessages([
                    'caja' => 'No hay una caja abierta. Debe abrir una caja antes de procesar devoluciones.',
                ]);
            }

            // Preparar items y calcular total a devolver
            $items = collect($request->productos)->filter(fn ($item) => (int) $item['cantidad'] > 0);
            
            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'productos' => 'Debe seleccionar al menos un producto a devolver.',
                ]);
            }

            $totalDevolucion = 0;
            $detallesParaGuardar = [];

            foreach ($items as $item) {
                $detalleVenta = VentaDetalle::where('venta_id', $venta->id)
                    ->where('producto_id', $item['producto_id'])
                    ->first();

                if (!$detalleVenta) {
                    throw ValidationException::withMessages([
                        'productos' => 'El producto no pertenece a esta venta.',
                    ]);
                }

                $cantidadDevueltaAnterior = DevolucionDetalle::whereHas('devolucion', function ($query) use ($venta) {
                    $query->where('venta_id', $venta->id);
                })
                    ->where('producto_id', $item['producto_id'])
                    ->sum('cantidad');

                $cantidadSolicitada = (int) $item['cantidad'];
                $cantidadDisponible = $detalleVenta->cantidad - $cantidadDevueltaAnterior;

                if ($cantidadSolicitada > $cantidadDisponible || $cantidadSolicitada <= 0) {
                    throw ValidationException::withMessages([
                        'productos' => 'Cantidad inválida en devolución para el producto ' . $detalleVenta->producto->nombre . '.',
                    ]);
                }

                $subtotal = $detalleVenta->precio_unitario * $cantidadSolicitada;
                $totalDevolucion += $subtotal;

                $detallesParaGuardar[] = [
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $cantidadSolicitada,
                    'precio_unitario' => $detalleVenta->precio_unitario,
                    'subtotal' => $subtotal,
                ];
            }

            // VALIDACIÓN CRÍTICA: Verificar que hay saldo en caja
            $validacionCaja = $cajaService->validarDevolucion($caja, $totalDevolucion);
            
            if (!$validacionCaja['valido']) {
                throw ValidationException::withMessages([
                    'caja' => $validacionCaja['mensaje'],
                ]);
            }

            // Crear devolución
            $devolucion = Devolucion::create([
                'venta_id' => $venta->id,
                'usuario_id' => auth()->id(),
                'tipo_devolucion' => $request->tipo_devolucion,
                'total_devuelto' => $totalDevolucion,
                'fecha_devolucion' => now(),
            ]);

            // Guardar detalles de devolución y actualizar stock
            foreach ($detallesParaGuardar as $detalle) {
                DevolucionDetalle::create([
                    'devolucion_id' => $devolucion->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                $producto = Producto::findOrFail($detalle['producto_id']);
                $producto->increment('stock', $detalle['cantidad']);
            }

            // Registrar movimiento en caja
            $cajaService->registrarMovimiento('devolucion', $totalDevolucion, $venta->id, "Devolución venta #{$venta->id}");

            DB::commit();

            return redirect()->route('cajero.devoluciones.show', $devolucion)
                ->with('success', 'Devolución procesada correctamente. Saldo en caja: $' . number_format($validacionCaja['saldo_disponible'], 2));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar devolución', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);

            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw $e;
        }
    }

    public function show(Devolucion $devolucion)
    {
        if ($devolucion->venta->sucursal_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para ver esta devolución.');
        }

        $devolucion->load(['venta.cliente', 'venta.usuario', 'detalles.producto', 'usuario']);

        return view('cajero.devoluciones.show', compact('devolucion'));
    }

    public function downloadPDF(Devolucion $devolucion)
    {
        if ($devolucion->venta->sucursal_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para descargar esta devolución.');
        }

        $devolucion->load(['venta.cliente', 'venta.usuario', 'detalles.producto', 'usuario']);

        $pdf = Pdf::loadView('cajero.devoluciones.pdf', compact('devolucion'))
            ->setPaper('letter')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->download('nota-credito-' . $devolucion->id . '.pdf');
    }
}
