<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Resumen de Devolución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Devolución #{{ $devolucion->id }}</h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Procesada el {{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('cajero.devoluciones.downloadPDF', $devolucion) }}" class="bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold inline-flex items-center gap-2">
                                
                                Descargar PDF
                            </a>
                            <a href="{{ route('cajero.ventas.index') }}" class="bg-gray-600 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Volver al Historial</a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-200 px-6 py-4 rounded-lg mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Venta relacionada</h3>
                            <p class="text-gray-700 dark:text-gray-300">Venta #: <span class="font-semibold">{{ $devolucion->venta->id }}</span></p>
                            <p class="text-gray-700 dark:text-gray-300">Cliente: <span class="font-semibold">{{ $devolucion->venta->cliente?->nombre_completo ?? 'Consumidor Final' }}</span></p>
                            <p class="text-gray-700 dark:text-gray-300">Total venta: <span class="font-semibold">${{ number_format($devolucion->venta->total, 2) }}</span></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Detalles de devolución</h3>
                            <p class="text-gray-700 dark:text-gray-300">Tipo: <span class="font-semibold">{{ ucfirst($devolucion->tipo_devolucion) }}</span></p>
                            <p class="text-gray-700 dark:text-gray-300">Cajero: <span class="font-semibold">{{ $devolucion->usuario->name }}</span></p>
                            <p class="text-gray-700 dark:text-gray-300">Total devuelto: <span class="font-semibold text-green-700 dark:text-green-400">${{ number_format($devolucion->total_devuelto, 2) }}</span></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-6 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                        <table class="min-w-full bg-white dark:bg-gray-800">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Producto</th>
                                    <th class="px-4 py-3 text-center font-semibold">Cantidad</th>
                                    <th class="px-4 py-3 text-right font-semibold">Precio Unit.</th>
                                    <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($devolucion->detalles as $detalle)
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $detalle->producto->nombre }}</td>
                                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $detalle->cantidad }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">${{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 text-right">
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total devuelto: <span class="text-green-700 dark:text-green-400">${{ number_format($devolucion->total_devuelto, 2) }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
