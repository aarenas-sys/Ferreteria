<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle de Venta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Venta #{{ $venta->id }}</h1>
                <a href="{{ route('cajero.ventas.create') }}" class="bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-semibold shadow-md transition duration-200">Nueva Venta</a>
            </div>

            @if(session('success'))
                <div class="bg-green-500 dark:bg-green-600 border border-green-600 dark:border-green-700 text-white px-6 py-4 rounded-lg mb-6 shadow-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if($errors->has('error'))
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-6 py-4 rounded-lg mb-6 shadow-lg">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-50 dark:from-gray-700 to-blue-100 dark:to-gray-800 p-6 rounded-lg shadow-md border border-blue-200 dark:border-gray-600">
                    <h2 class="text-xl font-bold text-blue-900 dark:text-blue-300 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Información General
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Fecha:</span>
                            <span class="text-blue-900 dark:text-blue-100 font-medium">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Tipo:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $venta->tipo_venta === 'contado' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white' }}">
                                {{ ucfirst($venta->tipo_venta) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Estado:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-bold
                                {{ $venta->estado === 'completada' ? 'bg-green-500 text-white' : ($venta->estado === 'pendiente_pago' ? 'bg-yellow-500 text-black' : 'bg-red-500 text-white') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Cajero:</span>
                            <span class="text-blue-900 dark:text-blue-100 font-medium">{{ $venta->usuario->name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                            <span class="font-semibold text-blue-800 dark:text-blue-300">Sucursal:</span>
                            <span class="text-blue-900 dark:text-blue-100 font-medium">{{ $venta->sucursal->name }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="font-semibold text-blue-800">Cliente:</span>
                            <span class="text-blue-900 font-medium">
                                {{ $venta->cliente ? $venta->cliente->nombre_completo . ' - ' . $venta->cliente->documento : 'No registrado' }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($venta->credito)
                <div class="bg-gradient-to-br from-yellow-50 dark:from-gray-700 to-yellow-100 dark:to-gray-800 p-6 rounded-lg shadow-md border border-yellow-200 dark:border-gray-600">
                    <h2 class="text-xl font-bold text-yellow-900 dark:text-yellow-300 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        Información de Crédito
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-yellow-200 dark:border-gray-600">
                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Monto Total:</span>
                            <span class="text-yellow-900 dark:text-yellow-100 font-bold">${{ number_format($venta->credito->monto_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-yellow-200 dark:border-gray-600">
                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Saldo Pendiente:</span>
                            <span class="text-red-600 dark:text-red-400 font-bold">${{ number_format($venta->credito->saldo_pendiente, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-yellow-200 dark:border-gray-600">
                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Fecha Inicio:</span>
                            <span class="text-yellow-900 dark:text-yellow-100 font-medium">{{ \Carbon\Carbon::parse($venta->credito->fecha_inicio)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-yellow-200 dark:border-gray-600">
                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Fecha Vencimiento:</span>
                            <span class="text-yellow-900 dark:text-yellow-100 font-medium">{{ \Carbon\Carbon::parse($venta->credito->fecha_vencimiento)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="font-semibold text-yellow-800 dark:text-yellow-300">Estado:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-bold
                                {{ $venta->credito->estado === 'pendiente' ? 'bg-yellow-500 text-black' : ($venta->credito->estado === 'pagado' ? 'bg-green-500 text-white' : 'bg-red-500 text-white') }}">
                                {{ ucfirst($venta->credito->estado) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                @if($venta->credito && $venta->credito->saldo_pendiente > 0)
                    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-900 dark:text-blue-200">💡 Usa el apartado de <strong>Pagos de Crédito</strong> en el dashboard para registrar el pago de este crédito.</p>
                    </div>
                @endif
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                    Productos Vendidos
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-lg rounded-lg overflow-hidden">
                        <thead class="bg-gradient-to-r from-green-600 to-green-700 dark:from-green-800 dark:to-green-900 text-white">
                            <tr>
                                <th class="px-6 py-4 border-b text-left font-semibold">Producto</th>
                                <th class="px-6 py-4 border-b text-right font-semibold">Cantidad</th>
                                <th class="px-6 py-4 border-b text-right font-semibold">Precio Unitario</th>
                                <th class="px-6 py-4 border-b text-right font-semibold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($venta->detalles as $detalle)
                            <tr class="hover:bg-green-50 dark:hover:bg-gray-700 transition duration-150">
                                <td class="px-6 py-4 border-b font-medium text-gray-900 dark:text-gray-100">{{ $detalle->producto->nombre }}</td>
                                <td class="px-6 py-4 border-b text-right font-semibold text-blue-600 dark:text-blue-400">{{ $detalle->cantidad }}</td>
                                <td class="px-6 py-4 border-b text-right font-semibold text-green-600 dark:text-green-400">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="px-6 py-4 border-b text-right font-bold text-green-700 dark:text-green-300">${{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gradient-to-r from-gray-800 dark:from-gray-900 to-gray-900 dark:to-black p-8 rounded-xl shadow-2xl text-white">
                <h2 class="text-2xl font-bold mb-6 text-center text-white">Resumen de Totales</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-2">Subtotal</label>
                        <span class="text-2xl font-bold text-blue-400 dark:text-blue-300">${{ number_format($venta->subtotal, 2) }}</span>
                    </div>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-2">IVA</label>
                        <span class="text-2xl font-bold text-yellow-400 dark:text-yellow-300">${{ number_format($venta->iva, 2) }}</span>
                    </div>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-2">Descuento</label>
                        <span class="text-2xl font-bold text-red-400 dark:text-red-300">${{ number_format($venta->descuento, 2) }}</span>
                    </div>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-2">Total</label>
                        <span class="text-4xl font-black text-green-400 dark:text-green-300 border-4 border-green-400 dark:border-green-600 rounded-lg px-4 py-2 bg-green-900 dark:bg-green-950">${{ number_format($venta->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('cajero') }}" class="bg-gray-500 dark:bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-600 dark:hover:bg-gray-700">Volver al Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>