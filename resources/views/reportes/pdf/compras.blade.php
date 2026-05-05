<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Compras</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        .summary { margin-bottom: 15px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; }
        .summary-table th { background-color: #f5f5f5; font-weight: bold; }
        .compra-section { page-break-inside: avoid; margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; }
        .compra-header { background-color: #f9f9f9; padding: 8px; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
        .compra-header h3 { margin: 0 0 5px 0; font-size: 12px; }
        .compra-info { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px; font-size: 10px; }
        .compra-info-item { }
        .compra-info-label { font-weight: bold; color: #333; }
        .compra-info-value { color: #666; }
        .productos-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .productos-table th, .productos-table td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 9px; }
        .productos-table th { background-color: #efefef; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .subtotal-row { background-color: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Compras a Proveedores</h1>
        <p>Sucursal: {{ $sucursal ? $sucursal->name : 'Todas las sucursales' }}</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
        @if($request->filled('fecha_inicio') || $request->filled('fecha_fin'))
            <p>Período: {{ $request->fecha_inicio ?? 'Inicio' }} - {{ $request->fecha_fin ?? 'Fin' }}</p>
        @endif
    </div>

    <div class="summary">
        <h2 style="font-size: 13px; margin: 10px 0;">Resumen General de Compras</h2>
        <table class="summary-table">
            <tr>
                <th>Total de Compras</th>
                <td>{{ $compras->count() }}</td>
                <th>Compras Recibidas</th>
                <td>{{ $compras->where('estado', 'recibida')->count() }}</td>
                <th>Compras Pendientes</th>
                <td>{{ $compras->where('estado', 'pendiente')->count() }}</td>
            </tr>
            <tr>
                <th>Compras Parciales</th>
                <td>{{ $compras->where('estado', 'parcial')->count() }}</td>
                <th>Total Estimado</th>
                <td>${{ number_format($compras->sum('total_estimado'), 2) }}</td>
                <th>Total Real</th>
                <td>${{ number_format($compras->sum('total_real') ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Detalle de cada compra -->
    @forelse($compras as $compra)
    <div class="compra-section">
        <div class="compra-header">
            <h3>Compra #{{ $compra->id }} - {{ $compra->created_at->format('d/m/Y H:i') }}</h3>
        </div>

        <div class="compra-info">
            <div class="compra-info-item">
                <div class="compra-info-label">Proveedor:</div>
                <div class="compra-info-value">{{ $compra->proveedor ? $compra->proveedor->nombre : 'Sin proveedor' }}</div>
            </div>
            <div class="compra-info-item">
                <div class="compra-info-label">Supervisor:</div>
                <div class="compra-info-value">{{ $compra->usuario ? $compra->usuario->name : 'Sin usuario' }}</div>
            </div>
            <div class="compra-info-item">
                <div class="compra-info-label">Sucursal:</div>
                <div class="compra-info-value">{{ $compra->sucursal ? $compra->sucursal->name : 'N/A' }}</div>
            </div>
            <div class="compra-info-item">
                <div class="compra-info-label">Estado:</div>
                <div class="compra-info-value">{{ ucfirst(str_replace('_', ' ', $compra->estado)) }}</div>
            </div>
        </div>

        <!-- Tabla de productos -->
        @if($compra->detalles->count() > 0)
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Solicitada</th>
                        <th class="text-right">Recibida</th>
                        <th class="text-right">Pendiente</th>
                        <th class="text-right">Precio Compra</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->producto ? $detalle->producto->nombre : 'Producto eliminado' }}</td>
                        <td class="text-right">{{ $detalle->cantidad_solicitada }}</td>
                        <td class="text-right">{{ $detalle->cantidad_recibida }}</td>
                        <td class="text-right">{{ $detalle->cantidad_solicitada - $detalle->cantidad_recibida }}</td>
                        <td class="text-right">${{ number_format($detalle->precio_compra, 2) }}</td>
                        <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="4" class="text-right">TOTAL:</td>
                        <td colspan="2" class="text-right">${{ number_format($compra->detalles->sum('subtotal'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top: 8px; font-size: 10px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div><strong>Total Estimado:</strong> ${{ number_format($compra->total_estimado, 2) }}</div>
                <div><strong>Total Real:</strong> ${{ number_format($compra->total_real ?? $compra->total_estimado, 2) }}</div>
                @php
                    $diferencia = abs(($compra->total_real ?? $compra->total_estimado) - $compra->total_estimado);
                @endphp
                <div><strong>Diferencia:</strong> ${{ number_format($diferencia, 2) }}</div>
            </div>
        @else
            <p style="text-align: center; color: #999; padding: 10px;">No hay productos registrados en esta compra.</p>
        @endif
    </div>
    @empty
    <p style="text-align: center; color: #999; padding: 20px;">No se encontraron compras en el período seleccionado.</p>
    @endforelse

    <div class="footer">
        <p>Este documento fue generado automáticamente por el sistema FerreNet el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>