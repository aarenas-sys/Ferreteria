<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        .summary { margin-bottom: 20px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .summary-table th { background-color: #f5f5f5; font-weight: bold; }
        .total-row { background-color: #e8f4f8; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status-contado { background-color: #d4edda; }
        .status-credito { background-color: #cce5ff; }
        .status-completada { background-color: #d4edda; }
        .status-pendiente { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Sucursal: {{ $sucursal ? $sucursal->name : 'Todas las sucursales' }}</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
        @if($request->filled('fecha_inicio') || $request->filled('fecha_fin'))
            <p>Período: {{ $request->fecha_inicio ?? 'Inicio' }} - {{ $request->fecha_fin ?? 'Fin' }}</p>
        @endif
    </div>

    <div class="summary">
        <h2>Resumen de Ventas</h2>
        <table class="summary-table">
            <tr>
                <th>Ventas al Contado</th>
                <td class="text-right"><strong>${{ number_format($totales['ventas_contado'], 2) }}</strong></td>
            </tr>
            <tr>
                <th>Ventas a Crédito</th>
                <td class="text-right"><strong>${{ number_format($totales['ventas_credito'], 2) }}</strong></td>
            </tr>
            <tr class="total-row">
                <th>Total Ventas</th>
                <td class="text-right"><strong>${{ number_format($totales['ventas_contado'] + $totales['ventas_credito'], 2) }}</strong></td>
            </tr>
        </table>

        <h2>Flujo Real de Dinero</h2>
        <table class="summary-table">
            <tr>
                <th>Ingresos Contado</th>
                <td class="text-right"><strong>${{ number_format($totales['ventas_contado'], 2) }}</strong></td>
            </tr>
            <tr>
                <th>Pagos de Crédito</th>
                <td class="text-right"><strong>${{ number_format($totales['pagos_credito'], 2) }}</strong></td>
            </tr>
            <tr>
                <th>Devoluciones</th>
                <td class="text-right"><strong>-${{ number_format($totales['devoluciones'], 2) }}</strong></td>
            </tr>
            <tr class="total-row">
                <th>Total Caja Real</th>
                <td class="text-right"><strong>${{ number_format($totales['total_general'], 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <h2>Detalle de Ventas</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th class="text-right">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                <td>{{ $venta->usuario->name }}</td>
                <td>{{ $venta->cliente ? $venta->cliente->nombre_completo : 'Cliente General' }}</td>
                <td class="status-{{ $venta->tipo_venta }}">{{ ucfirst($venta->tipo_venta) }}</td>
                <td class="text-right"><strong>${{ number_format($venta->total, 2) }}</strong></td>
                <td class="status-{{ $venta->estado }}">{{ ucfirst($venta->estado) }}</td>
            </tr>
            @if($venta->detalles && count($venta->detalles) > 0)
            <tr style="background-color: #f9fafb;">
                <td colspan="6" style="padding: 8px; font-size: 10px;">
                    <strong>Productos:</strong>
                    @foreach($venta->detalles as $detalle)
                        <div style="margin: 2px 0;">
                            • {{ $detalle->producto->codigo ?? 'N/A' }} - {{ $detalle->producto->nombre ?? 'Producto eliminado' }} 
                            (x{{ $detalle->cantidad }} @ ${{ number_format($detalle->precio_unitario, 2) }} = ${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }})
                        </div>
                    @endforeach
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="6" class="text-center">No se encontraron ventas en el período seleccionado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>