<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background: white;
        }
        body {
            padding: 20px;
            margin-bottom: 100px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 4px solid #2563eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(to right, #2563eb, #1e40af);
            color: white;
            padding: 20px;
            page-break-inside: avoid;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .company-info h1 {
            font-size: 28px;
            margin-bottom: 3px;
            color: #1a1a1a;
        }
        .company-info p {
            font-size: 11px;
            color: #2563eb;
            margin: 1px 0;
            font-weight: 500;
        }
        .contact-info {
            text-align: right;
            font-size: 10px;
            color: #2563eb;
            line-height: 1.4;
            font-weight: 500;
        }
        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #fff;
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 3px;
            margin-top: 10px;
        }
        .section {
            page-break-inside: avoid;
        }
        .section-title {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
            margin: 0;
            border: 1px solid #93c5fd;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid-row {
            display: table-row;
        }
        .info-column {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            font-size: 11px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #2563eb;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .info-value {
            color: #333;
            font-size: 11px;
            line-height: 1.3;
        }
        .customer-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .customer-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            font-size: 10px;
            vertical-align: top;
        }
        .customer-title {
            font-weight: bold;
            color: #2563eb;
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 3px;
        }
        .customer-name {
            font-weight: bold;
            font-size: 12px;
            color: #333;
            margin-bottom: 3px;
        }
        .customer-detail {
            color: #666;
            font-size: 10px;
            margin: 1px 0;
            line-height: 1.3;
        }
        .products-section {
            margin: 10px 0;
            page-break-inside: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table thead tr {
            background: linear-gradient(to right, #2563eb, #1d4ed8);
            color: white;
            page-break-inside: avoid;
        }
        table th {
            border: 1px solid #1d4ed8;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        table td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            color: #333;
            vertical-align: middle;
        }
        table tbody tr {
            page-break-inside: avoid;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table tbody tr:nth-child(odd) {
            background: white;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .product-code {
            font-family: monospace;
            color: #999;
            font-size: 9px;
        }
        .totals-box {
            margin: 10px 0;
            page-break-inside: avoid;
        }
        .totals-container {
            margin-left: auto;
            width: 280px;
            border: 2px solid #bfdbfe;
            background: white;
            padding: 10px;
            border-radius: 3px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
            line-height: 1.3;
        }
        .total-row:last-child {
            border-bottom: none;
        }
        .total-label {
            font-weight: bold;
            color: #333;
        }
        .total-value {
            font-family: monospace;
            font-weight: bold;
            text-align: right;
            color: #333;
        }
        .final-total {
            background: linear-gradient(to right, #2563eb, #1e40af);
            color: white;
            padding: 8px;
            border-radius: 3px;
            margin-top: 6px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }
        .final-total-label {
            font-weight: bold;
        }
        .final-total-value {
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }
        .discount-row {
            background: #fee2e2 !important;
        }
        .discount-row .total-label {
            color: #991b1b;
        }
        .discount-row .total-value {
            color: #991b1b;
        }
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 9px;
            line-height: 1.4;
            page-break-inside: avoid;
            border-top: 2px solid #1d4ed8;
        }
        .footer-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .footer-content {
            margin: 1px 0;
        }
        @page {
            size: A4 portrait;
            margin: 10px;
            margin-bottom: 70px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
                margin-bottom: 80px;
            }
            .container {
                max-width: 100%;
                box-shadow: none;
            }
        }
        .no-print {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <!-- Logo -->
                <td style="width: 80px; vertical-align: middle;">
                    <img src="{{ public_path('images/logo-login.png') }}" style="height: 60px;">
                </td>

                <!-- Empresa -->
                <td style="vertical-align: middle;">
                    <h1 style="color: #2563eb; margin: 0;">FerreNet</h1>
                    <p style="color: #2563eb; margin: 0;">Sistema de Gestión de Ferretería</p>
                    <p style="color: #2563eb; margin: 0;">NIT: 123.456.789-0</p>
                </td>

                <!-- Contacto -->
                <td style="text-align: right; vertical-align: middle;">
                    <div>Dirección: Calle Principal #123</div>
                    <div>Ciudad, País</div>
                    <div>Tel: (123) 456-7890</div>
                    <div>Email: info@ferenet.com</div>
                </td>
            </tr>
        </table>

        <!-- Información de la Venta -->
        <div class="section" style="margin: 0;">
            <div class="info-grid">
                <div class="info-grid-row">
                    <div class="info-column">
                        <div class="info-label">SUCURSAL</div>
                        <div class="info-value" style="font-weight: bold;">{{ $venta->sucursal->name ?? 'Principal' }}</div>
                        <div class="info-value">{{ $venta->sucursal->address ?? '' }}</div>
                    </div>
                    <div class="info-column">
                        <div class="info-label">FECHA Y HORA</div>
                        <div class="info-value" style="font-weight: bold;">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-column">
                        <div class="info-label">TIPO DE VENTA</div>
                        <div class="info-value" style="font-weight: bold;">{{ ucfirst($venta->tipo_venta) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cliente y Cajero -->
        <div class="section" style="margin: 0; page-break-inside: avoid;">
            <div class="customer-grid">
                <div style="display: table-row;">
                    <div class="customer-cell">
                        <div class="customer-title">CLIENTE</div>
                        @if($venta->cliente)
                            <div class="customer-name">{{ $venta->cliente->nombre_completo }}</div>
                            @if($venta->cliente->documento)
                                <div class="customer-detail"><strong>Documento:</strong> {{ $venta->cliente->documento }}</div>
                            @endif
                            @if($venta->cliente->telefono)
                                <div class="customer-detail"><strong>Teléfono:</strong> {{ $venta->cliente->telefono }}</div>
                            @endif
                            @if($venta->cliente->email)
                                <div class="customer-detail"><strong>Email:</strong> {{ $venta->cliente->email }}</div>
                            @endif
                            @if($venta->cliente->direccion)
                                <div class="customer-detail"><strong>Dirección:</strong> {{ $venta->cliente->direccion }}</div>
                            @endif
                        @else
                            <div class="customer-name">Consumidor Final</div>
                        @endif
                    </div>
                    <div class="customer-cell">
                        <div class="customer-title">CAJERO</div>
                        <div class="customer-name">{{ $venta->usuario->name }}</div>
                        <div class="customer-detail"><strong>ID:</strong> {{ $venta->usuario->id }}</div>
                        @if($venta->usuario->email)
                            <div class="customer-detail">{{ $venta->usuario->email }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="products-section" style="margin-bottom: 80px;">
            <h3 class="section-title">DETALLE DE PRODUCTOS</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Código</th>
                        <th style="width: 45%;">Producto</th>
                        <th style="width: 10%; text-align: center;">Cantidad</th>
                        <th style="width: 16%; text-align: right;">Precio Unit.</th>
                        <th style="width: 17%; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $detalle)
                    <tr>
                        <td class="product-code">{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ $detalle->cantidad }}</td>
                        <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="totals-box">
            <div class="totals-container">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">${{ number_format($venta->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">IVA:</span>
                    <span class="total-value">${{ number_format($venta->iva, 2) }}</span>
                </div>
                @if($venta->descuento > 0)
                <div class="total-row discount-row">
                    <span class="total-label">Descuento:</span>
                    <span class="total-value">-${{ number_format($venta->descuento, 2) }}</span>
                </div>
                @endif
                <div class="final-total">
                    <span class="final-total-label">TOTAL A PAGAR:</span>
                    <span class="final-total-value">${{ number_format($venta->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Fijo -->
    <footer>
        <div class="footer-title">Formas de Pago</div>
        <div class="footer-content">Efectivo • Tarjeta • Transferencia</div>
        <div class="footer-content" style="margin-top: 3px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 3px;">
            <div>¡Gracias por su preferencia!</div>
            <div>FerreNet - Tu ferretería de confianza</div>
            <div style="font-size: 8px; margin-top: 1px;">{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </footer>
</body>
</html>
