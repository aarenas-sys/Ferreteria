<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Comprobante de Venta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Botones de acción -->
                    <div class="flex justify-between items-center mb-6 gap-3">
                        <a href="{{ route('cajero.ventas.facturaPDF', $venta) }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition duration-200 inline-flex items-center gap-2">
                            
                            Descargar PDF
                        </a>
                        <a href="{{ route('cajero.ventas.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </a>
                    </div>

                    <!-- Comprobante de Venta Profesional -->
                    <div id="factura" class="max-w-4xl mx-auto bg-white border-4 border-blue-600 shadow-2xl">
                        <!-- Encabezado con Logo y Información -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                            <div class="flex items-center justify-between">
                                <!-- Logo e Información de la Empresa -->
                                <div class="flex items-center gap-4">
                                    <!-- Logo Placeholder - Reemplazar con imagen real -->
                                    <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center shadow-lg">
                                        <img src="{{ asset('images/logo-login.png') }}" alt="Logo FerreNet" class="w-12 h-12 object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                                        <div class="hidden text-blue-600 font-bold text-lg">FN</div>
                                    </div>
                                    <div>
                                        <h1 class="text-3xl font-bold mb-1">FerreNet</h1>
                                        <p class="text-blue-100 text-sm">Sistema de Gestión de Ferretería</p>
                                        <p class="text-blue-100 text-xs">NIT: 123.456.789-0</p>
                                    </div>
                                </div>

                                <!-- Información de Contacto -->
                                <div class="text-right text-sm">
                                    <p class="font-semibold">📍 Dirección:</p>
                                    <p class="text-blue-100">Calle Principal #123</p>
                                    <p class="text-blue-100">Ciudad, País</p>
                                    <p class="text-blue-100 mt-2">📞 Tel: (123) 456-7890</p>
                                    <p class="text-blue-100">✉️ info@ferenet.com</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Venta -->
                        <div class="bg-gray-50 p-6 border-b-2 border-blue-600">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Sucursal y Fecha -->
                                <div class="bg-white p-4 rounded-lg shadow-sm border">
                                    <h3 class="font-bold text-gray-800 mb-2 text-sm uppercase tracking-wide">Sucursal</h3>
                                    <p class="text-gray-700 font-semibold">{{ $venta->sucursal->name ?? 'Principal' }}</p>
                                    <p class="text-gray-600 text-sm mt-2">{{ $venta->sucursal->address ?? 'Dirección no especificada' }}</p>
                                </div>

                                <!-- Fecha y Número -->
                                <div class="bg-white p-4 rounded-lg shadow-sm border">
                                    <h3 class="font-bold text-gray-800 mb-2 text-sm uppercase tracking-wide">Factura N°</h3>
                                    <p class="text-2xl font-bold text-blue-600">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    <p class="text-gray-600 text-sm mt-1">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</p>
                                </div>

                                <!-- Tipo de Venta -->
                                <div class="bg-white p-4 rounded-lg shadow-sm border">
                                    <h3 class="font-bold text-gray-800 mb-2 text-sm uppercase tracking-wide">Tipo de Venta</h3>
                                    <span class="px-3 py-1 rounded-full text-sm font-bold
                                        {{ $venta->tipo_venta === 'contado' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white' }}">
                                        {{ ucfirst($venta->tipo_venta) }}
                                    </span>
                                    @if($venta->tipo_venta === 'credito')
                                        <p class="text-gray-600 text-sm mt-2">Estado: <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $venta->estado)) }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Información del Cliente y Cajero -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Datos del Cliente -->
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-3 text-lg border-b-2 border-blue-600 pb-1">Cliente</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        @if($venta->cliente)
                                            <p class="font-semibold text-gray-800 text-lg">{{ $venta->cliente->nombre_completo }}</p>
                                            @if($venta->cliente->documento)
                                                <p class="text-gray-600">📋 Documento: <span class="font-semibold">{{ $venta->cliente->documento }}</span></p>
                                            @endif
                                            @if($venta->cliente->telefono)
                                                <p class="text-gray-600">📞 Teléfono: {{ $venta->cliente->telefono }}</p>
                                            @endif
                                            @if($venta->cliente->email)
                                                <p class="text-gray-600">✉️ Email: {{ $venta->cliente->email }}</p>
                                            @endif
                                            @if($venta->cliente->direccion)
                                                <p class="text-gray-600">📍 Dirección: {{ $venta->cliente->direccion }}</p>
                                            @endif
                                        @else
                                            <p class="font-semibold text-gray-800 text-lg">Consumidor Final</p>
                                            <p class="text-gray-600">Cliente no registrado</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Datos del Cajero -->
                                <div>
                                    <h3 class="font-bold text-gray-800 mb-3 text-lg border-b-2 border-blue-600 pb-1">Cajero</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="font-semibold text-gray-800 text-lg">{{ $venta->usuario->name }}</p>
                                        <p class="text-gray-600">👤 ID Usuario: {{ $venta->usuario->id }}</p>
                                        @if($venta->usuario->email)
                                            <p class="text-gray-600">✉️ {{ $venta->usuario->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Productos -->
                        <div class="p-6">
                            <h3 class="font-bold text-gray-800 mb-4 text-lg border-b-2 border-blue-600 pb-2">Detalle de Productos</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300 shadow-lg">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                            <th class="border border-blue-700 px-4 py-3 text-left font-bold text-sm">Código</th>
                                            <th class="border border-blue-700 px-4 py-3 text-left font-bold text-sm">Producto</th>
                                            <th class="border border-blue-700 px-4 py-3 text-center font-bold text-sm">Cant.</th>
                                            <th class="border border-blue-700 px-4 py-3 text-right font-bold text-sm">Precio Unit.</th>
                                            <th class="border border-blue-700 px-4 py-3 text-right font-bold text-sm">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($venta->detalles as $index => $detalle)
                                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition duration-150">
                                            <td class="border border-gray-300 px-4 py-3 font-mono text-sm text-gray-600">{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                                            <td class="border border-gray-300 px-4 py-3 font-medium text-gray-800">
                                                {{ $detalle->producto->nombre }}
                                                @if($detalle->producto->descripcion)
                                                    <br><span class="text-xs text-gray-500">{{ Str::limit($detalle->producto->descripcion, 50) }}</span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-300 px-4 py-3 text-center font-semibold text-gray-800">{{ $detalle->cantidad }}</td>
                                            <td class="border border-gray-300 px-4 py-3 text-right font-mono text-gray-800">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                            <td class="border border-gray-300 px-4 py-3 text-right font-mono font-semibold text-gray-800">${{ number_format($detalle->subtotal, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="bg-gray-50 p-6 border-t-2 border-blue-600">
                            <div class="flex justify-end">
                                <div class="w-80 bg-white p-6 rounded-lg shadow-lg border-2 border-blue-200">
                                    <h4 class="font-bold text-gray-800 mb-4 text-lg border-b border-gray-300 pb-2">Resumen de Pago</h4>

                                    <div class="space-y-2">
                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="font-medium text-gray-700">Subtotal:</span>
                                            <span class="font-mono text-gray-800">${{ number_format($venta->subtotal, 2) }}</span>
                                        </div>

                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="font-medium text-gray-700">IVA ({{ number_format(($venta->iva / $venta->subtotal) * 100, 1) }}%):</span>
                                            <span class="font-mono text-gray-800">${{ number_format($venta->iva, 2) }}</span>
                                        </div>

                                        @if($venta->descuento > 0)
                                        <div class="flex justify-between py-2 border-b border-red-200 bg-red-50 px-2 rounded">
                                            <span class="font-medium text-red-700">Descuento:</span>
                                            <span class="font-mono text-red-700">-${{ number_format($venta->descuento, 2) }}</span>
                                        </div>
                                        @endif

                                        <div class="flex justify-between py-3 border-t-2 border-blue-600 bg-blue-50 px-3 rounded mt-4">
                                            <span class="font-bold text-xl text-blue-800">TOTAL A PAGAR:</span>
                                            <span class="font-bold text-xl font-mono text-blue-800">${{ number_format($venta->total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional y Pie de Página -->
                        <div class="bg-blue-600 text-white p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                                <div>
                                    <h4 class="font-bold mb-2">💰 Formas de Pago</h4>
                                    <p class="text-sm text-blue-100">Efectivo, Tarjeta, Transferencia</p>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">⏰ Horarios de Atención</h4>
                                    <p class="text-sm text-blue-100">Lun - Vie: 8:00 - 17:00</p>
                                </div>
                                <div>
                                    <h4 class="font-bold mb-2">📋 Términos y Condiciones</h4>
                                    <p class="text-sm text-blue-100">Productos sujetos a disponibilidad</p>
                                </div>
                            </div>

                            <div class="text-center mt-6 pt-4 border-t border-blue-500">
                                <p class="text-lg font-bold mb-2">¡Gracias por su preferencia!</p>
                                <p class="text-blue-100">FerreNet - Tu ferretería de confianza</p>
                                <p class="text-xs text-blue-200 mt-2">Factura generada automáticamente por el sistema - {{ now()->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #factura, #factura * {
                visibility: visible;
            }
            #factura {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: none;
                box-shadow: none;
                border: 2px solid #2563eb !important;
                margin: 0;
                padding: 0;
                transform: scale(0.95);
                transform-origin: top center;
            }
            button, .no-print {
                display: none !important;
            }
            @page {
                margin: 0.5cm;
                size: A4;
            }
        }

        /* Estilos adicionales para mejor apariencia */
        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .border-blue-700 {
            border-color: #1d4ed8;
        }

        .bg-gradient-to-r {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
        }

        .from-blue-600 {
            --tw-gradient-from: #2563eb;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(37, 99, 235, 0));
        }

        .to-blue-800 {
            --tw-gradient-to: #1e40af;
        }

        .from-blue-600 {
            --tw-gradient-from: #2563eb;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(37, 99, 235, 0));
        }

        .to-blue-700 {
            --tw-gradient-to: #1d4ed8;
        }
    </style>
</x-app-layout>
