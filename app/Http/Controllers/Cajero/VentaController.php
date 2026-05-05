<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cajero\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Discount;
use App\Models\Producto;
use App\Models\Setting;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\CajaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::where('sucursal_id', auth()->user()->sucursal_id)
            ->with(['cliente', 'usuario'])
            ->withCount('devoluciones')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('cajero.ventas.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userBranchId = auth()->user()->branch_id;
        Log::info('Cargando productos para cajero', ['user_id' => auth()->id(), 'branch_id' => $userBranchId]);

        $productos = Producto::where('sucursal_id', $userBranchId)
            ->where('stock', '>', 0)
            ->get();

        Log::info('Productos encontrados', ['count' => $productos->count()]);

        $clientes = Cliente::all();
        $descuentos = Discount::active()
            ->forBranch($userBranchId)
            ->get();

        return view('cajero.ventas.create', compact('productos', 'clientes', 'descuentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        // Verificar autenticación y permisos
        if (!auth()->check()) {
            return redirect()->route('login')->withErrors(['error' => 'Debe iniciar sesión para crear una venta.']);
        }

        if (auth()->user()->role !== 'cajero') {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        // Verificar que el usuario tenga sucursal asignada
        $userBranchId = auth()->user()->branch_id;
        Log::info('Usuario branch_id', ['user_id' => auth()->id(), 'branch_id' => $userBranchId]);

        if (!$userBranchId) {
            Log::error('Usuario sin sucursal asignada', ['user_id' => auth()->id()]);
            return back()->withErrors(['error' => 'No tiene una sucursal asignada. Contacte al administrador.']);
        }

        DB::beginTransaction();

        try {
            $userBranchId = auth()->user()->branch_id;
            // 1. Validar productos y stock
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id']);

                // Verificar que el producto pertenece a la sucursal del cajero
                if ($producto->sucursal_id !== $userBranchId) {
                    throw ValidationException::withMessages([
                        'productos' => 'El producto "' . $producto->nombre . '" no pertenece a tu sucursal.'
                    ]);
                }

                // Verificar stock suficiente
                if ($producto->stock < $item['cantidad']) {
                    throw ValidationException::withMessages([
                        'productos' => 'Stock insuficiente para "' . $producto->nombre . '". Disponible: ' . $producto->stock
                    ]);
                }
            }

            // 2. Calcular subtotal
            $subtotal = 0;
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id']);
                $subtotal += $producto->precio * $item['cantidad'];
            }

            // 3. Obtener descuento seleccionado o aplicar descuento por defecto
            $descuento = 0;
            $descuentoAplicado = null;

            if ($request->filled('descuento_id')) {
                $descuentoSeleccionado = Discount::active()
                    ->forBranch($userBranchId)
                    ->find($request->descuento_id);

                if (!$descuentoSeleccionado) {
                    throw ValidationException::withMessages([
                        'descuento_id' => 'El descuento seleccionado no está disponible para tu sucursal.'
                    ]);
                }

                if ($descuentoSeleccionado->type === 'percentage') {
                    $descuento = $subtotal * ($descuentoSeleccionado->value / 100);
                } else {
                    $descuento = min($descuentoSeleccionado->value, $subtotal);
                }

                $descuentoAplicado = $descuentoSeleccionado;
            } else {
                $descuentoPorcentaje = Setting::where('key', 'discount')->value('value') ?? 0;
                $descuento = $subtotal * ($descuentoPorcentaje / 100);
            }

            // 4. Obtener IVA y calcular totales
            $ivaPorcentaje = Setting::where('key', 'iva')->value('value') ?? 0;
            $iva = $subtotal * ($ivaPorcentaje / 100);
            $total = $subtotal + $iva - $descuento;

            // 5. Validar cliente para crédito
            $cliente = null;
            if ($request->tipo_venta === 'credito') {
                $cliente = Cliente::findOrFail($request->cliente_id);

                if ($cliente->estado_credito !== 'activo') {
                    throw ValidationException::withMessages([
                        'cliente_id' => $cliente->estado_credito === 'mora'
                            ? 'Cliente en mora. No puede comprar a crédito.'
                            : 'El cliente no tiene estado de crédito activo.'
                    ]);
                }

                if (($cliente->saldo_actual + $total) > $cliente->cupo_credito) {
                    throw ValidationException::withMessages([
                        'cliente_id' => 'El total excede el cupo de crédito disponible.'
                    ]);
                }
            }

            // 6. Crear venta
            $venta = Venta::create([
                'usuario_id' => auth()->id(),
                'cliente_id' => $request->cliente_id,
                'sucursal_id' => $userBranchId,
                'descuento_id' => $descuentoAplicado?->id,
                'tipo_venta' => $request->tipo_venta,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'descuento' => $descuento,
                'total' => $total,
                'estado' => $request->tipo_venta === 'credito' ? 'pendiente_pago' : 'completada',
                'fecha_venta' => now(),
            ]);

            // 7. Crear detalles y descontar stock
            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id']);

                $subtotalItem = $producto->precio * $item['cantidad'];

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotalItem,
                ]);

                // Descontar stock
                $producto->decrement('stock', $item['cantidad']);
            }

            // 8. Si es crédito, crear registro de crédito y actualizar saldo del cliente
            if ($request->tipo_venta === 'credito') {
                Credito::create([
                    'venta_id' => $venta->id,
                    'cliente_id' => $cliente->id,
                    'monto_total' => $total,
                    'saldo_pendiente' => $total,
                    'fecha_inicio' => now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'estado' => 'pendiente',
                ]);

                $cliente->increment('saldo_actual', $total);
                $cliente->actualizarEstadoCredito();
            } else {
                $cajaService = new CajaService();
                $cajaService->registrarMovimiento('venta_contado', $total, $venta->id, "Venta contado #{$venta->id}");
            }

            DB::commit();

            Log::info('Venta creada exitosamente', [
                'venta_id' => $venta->id,
                'usuario_id' => auth()->id(),
                'total' => $total
            ]);

            return redirect()->route('cajero.ventas.show', $venta)
                ->with('success', 'Venta creada exitosamente.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear venta: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['error' => 'Error interno del servidor. Intente nuevamente.']);
        }
    }

    public function registrarPago(Request $request, Venta $venta)
    {
        if (!auth()->check() || auth()->user()->role !== 'cajero') {
            abort(403);
        }

        $userBranchId = auth()->user()->branch_id;

        if ($venta->sucursal_id !== $userBranchId) {
            abort(403);
        }

        if (! $venta->credito) {
            return back()->withErrors(['error' => 'Esta venta no tiene crédito asociado.']);
        }

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $venta->credito->saldo_pendiente,
        ]);

        DB::beginTransaction();

        try {
            $monto = (float) $validated['monto'];
            $credito = $venta->credito;
            $cliente = $venta->cliente;

            $credito->registrarPago($monto);
            $cliente->decrement('saldo_actual', $monto);
            $cliente->refresh();
            $cliente->actualizarEstadoCredito();

            $cajaService = new CajaService();
            $cajaService->registrarMovimiento('pago_credito', $monto, $venta->id, "Pago de crédito venta #{$venta->id}");

            if ($credito->saldo_pendiente <= 0 && $venta->estado === 'pendiente_pago') {
                $venta->estado = 'completada';
                $venta->save();
            }

            DB::commit();

            return back()->with('success', 'Pago registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar pago de crédito: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'user_id' => auth()->id(),
                'request' => $request->all(),
            ]);

            return back()->withErrors(['error' => 'Error interno al registrar el pago. Intente nuevamente.']);
        }
    }

    public function listarCreditos()
    {
        $userBranchId = auth()->user()->branch_id;

        $creditos = Credito::whereHas('venta', function ($query) use ($userBranchId) {
            $query->where('sucursal_id', $userBranchId);
        })
            ->where('estado', 'pendiente')
            ->with(['cliente', 'venta'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(15);

        return view('cajero.creditos.index', compact('creditos'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        // Asegurar que el cajero solo vea sus ventas o de su sucursal
        if ($venta->usuario_id !== auth()->id() && $venta->sucursal_id !== auth()->user()->sucursal_id) {
            abort(403);
        }

        $venta->load(['detalles.producto', 'cliente', 'usuario', 'credito']);

        return view('cajero.ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function factura(Venta $venta)
    {
        // Validación de seguridad: cajero solo puede ver ventas de su sucursal
        if ($venta->sucursal_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para ver esta venta.');
        }

        $venta->load([
            'detalles.producto',
            'cliente',
            'usuario'
        ]);

        return view('cajero.ventas.factura', compact('venta'));
    }

    public function facturaPDF(Venta $venta)
    {
        // Validación de seguridad: cajero solo puede ver ventas de su sucursal
        if ($venta->sucursal_id !== auth()->user()->branch_id) {
            abort(403, 'No tienes permiso para ver esta venta.');
        }

        $venta->load([
            'detalles.producto',
            'cliente',
            'usuario'
        ]);

        $pdf = Pdf::loadView('cajero.ventas.factura_pdf', compact('venta'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('factura-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
