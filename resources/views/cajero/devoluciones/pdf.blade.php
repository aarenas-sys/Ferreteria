<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Crédito #{{ $devolucion->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background: white;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 15px;
        }
        .header-left h1 {
            font-size: 28px;
            color: #dc2626;
            margin-bottom: 5px;
        }
        .header-left p {
            font-size: 12px;
            color: #666;
        }
        .header-right {
            text-align: right;
            font-size: 11px;
        }
        .header-right p {
            margin-bottom: 3px;
            line-height: 1.4;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            font-size: 12px;
        }
        .info-block h3 {
            background: #f3f4f6;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border-left: 3px solid #dc2626;
        }
        .info-block p {
            margin-bottom: 5px;
            line-height: 1.5;
        }
        .info-block strong {
            display: inline-block;
            width: 80px;
            color: #333;
        }
        .table-section {
            margin-bottom: 30px;
        }
        .table-section h3 {
            background: #f3f4f6;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border-left: 3px solid #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table thead {
            background: #f3f4f6;
            border-top: 1px solid #ddd;
            border-bottom: 2px solid #dc2626;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .summary-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #ddd;
            border-left: 3px solid #dc2626;
            font-size: 12px;
        }
        .summary-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
        }
        .summary-row span:first-child {
            display: inline-block;
            width: 150px;
            font-weight: bold;
            text-align: right;
            margin-right: 20px;
        }
        .summary-row span:last-child {
            display: inline-block;
            width: 80px;
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            padding-top: 8px;
            border-top: 2px solid #dc2626;
            font-size: 14px;
            margin-top: 10px;
        }
        .total-row span:first-child {
            display: inline-block;
            width: 150px;
            font-weight: bold;
            text-align: right;
            margin-right: 20px;
        }
        .total-row span:last-child {
            display: inline-block;
            width: 80px;
            text-align: right;
            font-weight: bold;
            color: #dc2626;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
            line-height: 1.6;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #dc2626;
            color: white;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(220, 38, 38, 0.1);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">NOTA DE CRÉDITO</div>
    
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>NOTA DE CRÉDITO</h1>
                <p>Documento de devolución de mercancía</p>
            </div>
            <div class="header-right">
                <p><strong>Nota de Crédito #:</strong> {{ $devolucion->id }}</p>
                <p><strong>Fecha:</strong> {{ $devolucion->fecha_devolucion->format('d/m/Y') }}</p>
                <p><strong>Hora:</strong> {{ $devolucion->fecha_devolucion->format('H:i') }}</p>
                <p><strong>Venta Original #:</strong> {{ $devolucion->venta->id }}</p>
            </div>
        </div>

        <!-- Information Section -->
        <div class="info-section">
            <div class="info-block">
                <h3>Datos de la Venta</h3>
                <p>
                    <strong>Venta #:</strong> {{ $devolucion->venta->id }}<br>
                    <strong>Fecha Venta:</strong> {{ \Carbon\Carbon::parse($devolucion->venta->fecha_venta)->format('d/m/Y') }}<br>
                    <strong>Tipo:</strong> {{ ucfirst($devolucion->venta->tipo_venta) }}
                </p>
            </div>
            <div class="info-block">
                <h3>Información del Cliente</h3>
                <p>
                    <strong>Cliente:</strong> {{ $devolucion->venta->cliente?->nombre_completo ?? 'Consumidor Final' }}<br>
                    @if($devolucion->venta->cliente)
                        <strong>Documento:</strong> {{ $devolucion->venta->cliente->documento }}<br>
                        <strong>Teléfono:</strong> {{ $devolucion->venta->cliente->telefono ?? 'N/A' }}
                    @endif
                </p>
            </div>
        </div>

        <div class="info-section" style="margin-bottom: 30px;">
            <div class="info-block">
                <h3>Procesada por</h3>
                <p>
                    <strong>Cajero:</strong> {{ $devolucion->usuario->name }}<br>
                    <strong>Sucursal:</strong> {{ $devolucion->venta->sucursal->name }}
                </p>
            </div>
            <div class="info-block">
                <h3>Tipo de Devolución</h3>
                <p>
                    <span class="badge">{{ strtoupper($devolucion->tipo_devolucion) }}</span>
                </p>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-section">
            <h3>Productos Devueltos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devolucion->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->nombre }}</td>
                            <td class="text-right">{{ $detalle->cantidad }}</td>
                            <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-right"><strong>${{ number_format($detalle->subtotal, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal Devuelto:</span>
                <span>${{ number_format($devolucion->total_devuelto, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Total Crédito:</span>
                <span>${{ number_format($devolucion->total_devuelto, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado automáticamente por el sistema de punto de venta</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
            <p style="margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px;">
                Esta nota de crédito representa una devolución de mercancía y puede ser utilizada para futuras compras o reembolso.
            </p>
        </div>
    </div>
</body>
</html>
