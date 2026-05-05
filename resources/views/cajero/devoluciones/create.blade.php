<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Devolución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold">Devolución de Venta #{{ $venta->id }}</h1>
                            <p class="text-sm text-gray-600 mt-1">Venta del {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }} - Total: ${{ number_format($venta->total, 2) }}</p>
                        </div>
                        <a href="{{ route('cajero.ventas.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold">Volver al Historial</a>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-3">Datos del Cliente</h3>
                            @if($venta->cliente)
                                <p class="text-gray-700 font-semibold">{{ $venta->cliente->nombre_completo }}</p>
                                @if($venta->cliente->documento)
                                    <p class="text-sm text-gray-600">Documento: {{ $venta->cliente->documento }}</p>
                                @endif
                                @if($venta->cliente->telefono)
                                    <p class="text-sm text-gray-600">Teléfono: {{ $venta->cliente->telefono }}</p>
                                @endif
                            @else
                                <p class="text-gray-700 font-semibold">Consumidor Final</p>
                            @endif
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-3">Condiciones de Devolución</h3>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                                <li>Solo ventas de contado.</li>
                                <li>Máximo 15 días desde la fecha de venta.</li>
                                <li>Se puede devolver parcial o totalmente.</li>
                                <li>El stock se reingresa automáticamente.</li>
                            </ul>
                        </div>
                    </div>

                    @if($venta->devoluciones->count() > 0)
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-4 rounded-lg mb-6">
                            <h3 class="font-semibold mb-2">Devoluciones previas registradas</h3>
                            <div class="space-y-3 text-sm text-gray-700">
                                @foreach($venta->devoluciones as $devolucion)
                                    <div class="bg-white rounded-lg p-3 border border-yellow-200">
                                        <p><strong>#{{ $devolucion->id }}</strong> - {{ ucfirst($devolucion->tipo_devolucion) }} - <span class="font-semibold">${{ number_format($devolucion->total_devuelto, 2) }}</span></p>
                                        <p class="text-xs text-gray-600">Procesada el {{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs text-gray-600"><a href="{{ route('cajero.devoluciones.show', $devolucion) }}" class="text-indigo-600 hover:underline">Ver detalle</a></p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('cajero.devoluciones.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="venta_id" value="{{ $venta->id }}">

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de devolución</label>
                            <select name="tipo_devolucion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecciona un tipo</option>
                                <option value="efectivo" {{ old('tipo_devolucion') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ old('tipo_devolucion') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto mb-6 border border-gray-200 rounded-lg shadow-sm">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Producto</th>
                                        <th class="px-4 py-3 text-center font-semibold">Vendida</th>
                                        <th class="px-4 py-3 text-center font-semibold">A devolver</th>
                                        <th class="px-4 py-3 text-right font-semibold">Precio</th>
                                        <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($venta->detalles as $index => $detalle)
                                        @php
                                            $available = $detalle->cantidad - ($returnedQuantities[$detalle->producto_id] ?? 0);
                                        @endphp
                                        <tr class="border-t border-gray-200">
                                            <td class="px-4 py-3 text-sm text-gray-800">{{ $detalle->producto->nombre }}</td>
                                            <td class="px-4 py-3 text-center text-gray-700">{{ $detalle->cantidad }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="productos[{{ $index }}][producto_id]" value="{{ $detalle->producto_id }}">
                                                <input
                                                    type="number"
                                                    name="productos[{{ $index }}][cantidad]"
                                                    min="0"
                                                    max="{{ $available }}"
                                                    value="{{ old('productos.' . $index . '.cantidad', 0) }}"
                                                    class="w-24 rounded-md border-gray-300 text-center shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                >
                                                <p class="text-xs text-gray-500 mt-1">Máx: {{ $available }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-700">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                            <td class="px-4 py-3 text-right text-gray-700">${{ number_format($detalle->precio_unitario * $available, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('cajero.ventas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-3 rounded-lg font-semibold">Cancelar</a>
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-3 rounded-lg font-semibold">Procesar Devolución</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
