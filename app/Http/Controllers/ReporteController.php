<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasExport;
use App\Exports\InventarioExport;
use App\Exports\ComprasExport;
use App\Models\Branch;
use App\Models\User;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        $sucursales = Branch::all();
        $usuarios = User::whereIn('role', ['cajero', 'supervisor'])->get();

        return view('reportes.index', compact('sucursales', 'usuarios'));
    }

    public function ventas(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'usuario_id' => 'nullable|exists:users,id',
            'tipo_venta' => 'nullable|in:contado,credito',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $sucursales = Branch::when(auth()->user()->role === 'supervisor', function ($q) {
            $q->where('id', auth()->user()->branch_id);
        })->get();

        $usuariosQuery = User::query();

        if (auth()->user()->role === 'supervisor') {
            $usuariosQuery->where('role', 'cajero')
                ->where('branch_id', auth()->user()->branch_id);
        } else {
            $usuariosQuery->whereIn('role', ['cajero', 'supervisor'])
                ->when(auth()->user()->role === 'admin', function ($q) {
                    $q->where('id', '<>', auth()->id());
                })
                ->when($request->filled('sucursal_id'), function ($q) use ($request) {
                    $q->where('branch_id', $request->sucursal_id);
                });
        }

        $usuarios = $usuariosQuery->get();

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        if (auth()->user()->role === 'supervisor' && ! $request->filled('sucursal_id')) {
            $request->merge(['sucursal_id' => auth()->user()->branch_id]);
        }

        $ventaQuery = Venta::with(['usuario', 'cliente', 'sucursal', 'detalles.producto'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('fecha_venta', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('fecha_venta', '<=', $request->fecha_fin);
            })
            ->when($request->filled('usuario_id'), function ($q) use ($request) {
                $q->where('usuario_id', $request->usuario_id);
            })
            ->when($request->filled('tipo_venta'), function ($q) use ($request) {
                $q->where('tipo_venta', $request->tipo_venta);
            });

        $ventas = $ventaQuery->orderBy('fecha_venta', 'desc')->get();

        $ventasContado = $ventas->where('tipo_venta', 'contado')->sum('total');
        $ventasCredito = $ventas->where('tipo_venta', 'credito')->sum('total');
        $ingresosContado = $ventasContado;

        $pagosCredito = MovimientoCaja::where('tipo', 'pago_credito')
            ->when($request->filled('sucursal_id'), function ($q) use ($request) {
                $q->whereHas('caja', function ($q) use ($request) {
                    $q->where('sucursal_id', $request->sucursal_id);
                });
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            })
            ->sum('monto');

        $devoluciones = MovimientoCaja::where('tipo', 'devolucion')
            ->when($request->filled('sucursal_id'), function ($q) use ($request) {
                $q->whereHas('caja', function ($q) use ($request) {
                    $q->where('sucursal_id', $request->sucursal_id);
                });
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            })
            ->sum('monto');

        $totales = [
            'ventas_contado' => $ventasContado,
            'ventas_credito' => $ventasCredito,
            'total_ventas' => $ventasContado + $ventasCredito,
            'ingresos_contado' => $ingresosContado,
            'pagos_credito' => $pagosCredito,
            'devoluciones' => $devoluciones,
            'total_caja' => $ingresosContado + $pagosCredito - $devoluciones,
        ];

        return view('reportes.ventas', compact('ventas', 'totales', 'request', 'sucursales', 'usuarios'));
    }

    public function inventario(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado_stock' => 'nullable|in:normal,bajo',
        ]);

        // Obtener sucursales disponibles
        $sucursales = Branch::when(auth()->user()->role === 'supervisor', function ($q) {
            $q->where('id', auth()->user()->branch_id);
        })->get();

        // Obtener categorías para el filtro
        $categorias = Categoria::ordenadas()->get();

        // Determinar sucursal a filtrar
        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        if (auth()->user()->role === 'supervisor' && !$request->filled('sucursal_id')) {
            $request->merge(['sucursal_id' => auth()->user()->branch_id]);
        }

        // Query base con relaciones
        $query = Producto::with(['categoria', 'sucursal'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('categoria_id'), function ($q) use ($request) {
                $q->byCategoria($request->categoria_id);
            })
            ->when($request->filled('estado_stock'), function ($q) use ($request) {
                $q->byEstadoStock($request->estado_stock);
            })
            ->orderBy('stock', 'asc');

        $productos = $query->get();

        // Calcular resumen
        $totalProductos = $productos->count();
        $productosStockBajo = $productos->filter(fn($p) => $p->estado_stock === 'bajo')->count();
        $valorInventario = $productos->sum(function ($p) {
            return ($p->ultimo_precio_compra ?? $p->precio) * $p->stock;
        });

        // Obtener sucursal seleccionada
        $sucursal = $branchId ? Branch::find($branchId) : null;

        return view('reportes.inventario', compact(
            'productos',
            'request',
            'sucursales',
            'categorias',
            'sucursal',
            'totalProductos',
            'productosStockBajo',
            'valorInventario'
        ));
    }

    public function compras(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $sucursales = Branch::when(auth()->user()->role === 'supervisor', function ($q) {
            $q->where('id', auth()->user()->branch_id);
        })->get();

        $proveedores = \App\Models\Proveedor::all();

        $usuarios = User::where('role', 'supervisor')
            ->when(auth()->user()->role === 'supervisor', function ($q) {
                $q->where('branch_id', auth()->user()->branch_id);
            })
            ->when(auth()->user()->role === 'admin' && $request->filled('sucursal_id'), function ($q) use ($request) {
                $q->where('branch_id', $request->sucursal_id);
            })
            ->get();

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        if (auth()->user()->role === 'supervisor' && ! $request->filled('sucursal_id')) {
            $request->merge(['sucursal_id' => auth()->user()->branch_id]);
        }

        $query = Compra::with(['proveedor', 'usuario', 'sucursal', 'detalles.producto'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            })
            ->when($request->filled('proveedor_id'), function ($q) use ($request) {
                $q->where('proveedor_id', $request->proveedor_id);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });

        $compras = $query->orderBy('created_at', 'desc')->get();

        return view('reportes.compras', compact('compras', 'request', 'sucursales', 'proveedores', 'usuarios'));
    }

    public function exportPDF(Request $request)
    {
        $tipo = $request->get('tipo', 'ventas');

        switch ($tipo) {
            case 'ventas':
                return $this->exportVentasPDF($request);
            case 'inventario':
                return $this->exportInventarioPDF($request);
            case 'compras':
                return $this->exportComprasPDF($request);
            default:
                return back()->with('error', 'Tipo de reporte no válido');
        }
    }

    public function exportExcel(Request $request)
    {
        $tipo = $request->get('tipo', 'ventas');

        switch ($tipo) {
            case 'ventas':
                return $this->exportVentasExcel($request);
            case 'inventario':
                return $this->exportInventarioExcel($request);
            case 'compras':
                return $this->exportComprasExcel($request);
            default:
                return back()->with('error', 'Tipo de reporte no válido');
        }
    }

    private function exportVentasPDF(Request $request)
    {
        // Obtener los datos de ventas
        $request->validate([
            'sucursal_id' => 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'usuario_id' => 'nullable|exists:users,id',
            'tipo_venta' => 'nullable|in:contado,credito',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $query = Venta::with(['usuario', 'cliente', 'sucursal', 'detalles.producto']);

        // Supervisores solo ven su sucursal
        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        if ($branchId) {
            $query->where('sucursal_id', $branchId);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('tipo_venta')) {
            $query->where('tipo_venta', $request->tipo_venta);
        }

        $ventas = $query->orderBy('fecha_venta', 'desc')->get();

        // Calcular totales
        $totales = [
            'ventas_contado' => $ventas->where('tipo_venta', 'contado')->sum('total'),
            'ventas_credito' => $ventas->where('tipo_venta', 'credito')->sum('total'),
            'pagos_credito' => 0,
            'devoluciones' => 0,
        ];

        // Calcular pagos de crédito y devoluciones solo si hay sucursal específica
        if ($branchId) {
            $totales['pagos_credito'] = MovimientoCaja::where('tipo', 'pago_credito')
                ->whereHas('caja', function($q) use ($branchId) {
                    $q->where('sucursal_id', $branchId);
                })
                ->when($request->filled('fecha_inicio'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->fecha_inicio);
                })
                ->when($request->filled('fecha_fin'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->fecha_fin);
                })
                ->sum('monto');

            $totales['devoluciones'] = MovimientoCaja::where('tipo', 'devolucion')
                ->whereHas('caja', function($q) use ($branchId) {
                    $q->where('sucursal_id', $branchId);
                })
                ->when($request->filled('fecha_inicio'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->fecha_inicio);
                })
                ->when($request->filled('fecha_fin'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->fecha_fin);
                })
                ->sum('monto');
        }

        $totales['total_general'] = $totales['ventas_contado'] + $totales['pagos_credito'] - $totales['devoluciones'];

        $sucursal = $branchId ? Branch::find($branchId) : null;

        $pdf = Pdf::loadView('reportes.pdf.ventas', compact('ventas', 'totales', 'request', 'sucursal'));
        return $pdf->download('reporte-ventas-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportInventarioPDF(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado_stock' => 'nullable|in:normal,bajo',
        ]);

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        // Query base con relaciones
        $query = Producto::with(['categoria', 'sucursal'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('categoria_id'), function ($q) use ($request) {
                $q->byCategoria($request->categoria_id);
            })
            ->when($request->filled('estado_stock'), function ($q) use ($request) {
                $q->byEstadoStock($request->estado_stock);
            })
            ->orderBy('stock', 'asc');

        $productos = $query->get();
        $sucursal = $branchId ? Branch::find($branchId) : null;

        $pdf = Pdf::loadView('reportes.pdf.inventario', compact('productos', 'request', 'sucursal'));
        return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportComprasPDF(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'usuario_id' => 'nullable|exists:users,id',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        $query = Compra::with(['proveedor', 'usuario', 'sucursal', 'detalles.producto'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            })
            ->when($request->filled('proveedor_id'), function ($q) use ($request) {
                $q->where('proveedor_id', $request->proveedor_id);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });

        $compras = $query->orderBy('created_at', 'desc')->get();
        $sucursal = Branch::find($branchId);

        $pdf = Pdf::loadView('reportes.pdf.compras', compact('compras', 'request', 'sucursal'));
        return $pdf->download('reporte-compras-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportVentasExcel(Request $request)
    {
        // Obtener los datos de ventas
        $request->validate([
            'sucursal_id' => 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'usuario_id' => 'nullable|exists:users,id',
            'tipo_venta' => 'nullable|in:contado,credito',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $query = Venta::with(['usuario', 'cliente', 'sucursal', 'detalles.producto']);

        // Supervisores solo ven su sucursal
        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        if ($branchId) {
            $query->where('sucursal_id', $branchId);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('tipo_venta')) {
            $query->where('tipo_venta', $request->tipo_venta);
        }

        $ventas = $query->orderBy('fecha_venta', 'desc')->get();

        return Excel::download(new VentasExport($ventas), 'reporte-ventas-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function exportInventarioExcel(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado_stock' => 'nullable|in:normal,bajo',
        ]);

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        // Query base con relaciones
        $query = Producto::with(['categoria', 'sucursal'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('categoria_id'), function ($q) use ($request) {
                $q->byCategoria($request->categoria_id);
            })
            ->when($request->filled('estado_stock'), function ($q) use ($request) {
                $q->byEstadoStock($request->estado_stock);
            })
            ->orderBy('stock', 'asc');

        $productos = $query->get();

        return Excel::download(new InventarioExport($productos), 'reporte-inventario-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function exportComprasExcel(Request $request)
    {
        $request->validate([
            'sucursal_id' => auth()->user()->role === 'supervisor' ? 'nullable' : 'nullable|exists:branches,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'usuario_id' => 'nullable|exists:users,id',
        ]);

        // Validar que fecha_fin no sea menor que fecha_inicio
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            if ($request->fecha_fin < $request->fecha_inicio) {
                return back()->withInput()->with('error', 'La fecha fin no puede ser menor que la fecha inicio.');
            }
        }

        $branchId = auth()->user()->role === 'supervisor'
            ? auth()->user()->branch_id
            : $request->sucursal_id;

        $query = Compra::with(['proveedor', 'usuario', 'sucursal', 'detalles.producto'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('sucursal_id', $branchId);
            })
            ->when($request->filled('fecha_inicio'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            })
            ->when($request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            })
            ->when($request->filled('proveedor_id'), function ($q) use ($request) {
                $q->where('proveedor_id', $request->proveedor_id);
            })
            ->when($request->filled('usuario_id'), function ($q) use ($request) {
                $q->where('usuario_id', $request->usuario_id);
            });

        $compras = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new ComprasExport($compras), 'reporte-compras-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function getUsuariosPorSucursal($sucursal_id)
    {
        $usuariosQuery = User::query();

        if (auth()->user()->role === 'supervisor') {
            $usuariosQuery->where('role', 'supervisor')
                ->where('branch_id', auth()->user()->branch_id);
        } else {
            $usuariosQuery->where('role', 'supervisor')
                ->where('branch_id', $sucursal_id)
                ->when(auth()->user()->role === 'admin', function ($q) {
                    $q->where('id', '<>', auth()->id());
                });
        }

        $usuarios = $usuariosQuery->get(['id', 'name']);

        return response()->json($usuarios);
    }
}