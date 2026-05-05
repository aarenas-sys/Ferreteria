<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .summary-table th { background-color: #f5f5f5; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status-normal { background-color: #d4edda; }
        .status-bajo { background-color: #f8d7da; }
        .status-agotado { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario</h1>
        <p>Sucursal: {{ $sucursal ? $sucursal->name : 'Sucursal' }}</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <h2>Resumen del Inventario</h2>
        <table class="summary-table">
            <tr>
                <th>Total de Productos</th>
                <td class="text-right">{{ count($productos) }}</td>
            </tr>
            <tr>
                <th>Productos con Stock Bajo (&lt;20)</th>
                <td class="text-right">{{ collect($productos)->filter(fn($p) => $p->estado_stock === 'bajo')->count() }}</td>
            </tr>
            <tr>
                <th>Valor Total Inventario</th>
                <td class="text-right">${{ number_format(collect($productos)->sum(fn($p) => ($p->ultimo_precio_compra ?? $p->precio) * $p->stock), 2) }}</td>
            </tr>
        </table>
    </div>

    <h2>Productos en Inventario</h2>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-right">Precio Compra</th>
                <th class="text-right">Precio Venta</th>
                <th class="text-right">Stock</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $producto)
            <tr>
                <td>{{ $producto->codigo }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>
                    @if($producto->categoria)
                        {{ $producto->categoria->nombre }}
                    @else
                        Sin categoría
                    @endif
                </td>
                <td class="text-right">
                    @if($producto->ultimo_precio_compra)
                        ${{ number_format($producto->ultimo_precio_compra, 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">${{ number_format($producto->precio_venta, 2) }}</td>
                <td class="text-right">{{ $producto->stock }}</td>
                <td class="status-{{ $producto->estado_stock === 'normal' ? 'normal' : 'bajo' }}">
                    {{ $producto->estado_stock === 'normal' ? 'Normal' : 'Stock Bajo' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No se encontraron productos con los filtros aplicados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>