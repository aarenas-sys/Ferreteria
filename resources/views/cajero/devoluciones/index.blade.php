<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Historial de Devoluciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Historial de Devoluciones</h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Registros de devoluciones procesadas por tu sucursal.</p>
                        </div>
                        <a href="{{ route('cajero.ventas.index') }}" class="bg-gray-600 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Volver al Historial de Ventas</a>
                    </div>

                    @if($devoluciones->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden shadow-lg">
                                <thead class="bg-gradient-to-r from-indigo-600 dark:from-indigo-800 to-indigo-700 dark:to-indigo-900 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold">ID Devolución</th>
                                        <th class="px-6 py-4 text-left font-semibold">Venta ID</th>
                                        <th class="px-6 py-4 text-left font-semibold">Cliente</th>
                                        <th class="px-6 py-4 text-left font-semibold">Tipo</th>
                                        <th class="px-6 py-4 text-right font-semibold">Total Devuelto</th>
                                        <th class="px-6 py-4 text-left font-semibold">Cajero</th>
                                        <th class="px-6 py-4 text-left font-semibold">Fecha</th>
                                        <th class="px-6 py-4 text-center font-semibold">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($devoluciones as $devolucion)
                                        <tr class="hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-150">
                                            <td class="px-6 py-4 border-b text-gray-900 dark:text-gray-100 font-medium">#{{ $devolucion->id }}</td>
                                            <td class="px-6 py-4 border-b text-gray-700 dark:text-gray-300">#{{ $devolucion->venta->id }}</td>
                                            <td class="px-6 py-4 border-b text-gray-700 dark:text-gray-300">{{ $devolucion->venta->cliente?->nombre_completo ?? 'Consumidor Final' }}</td>
                                            <td class="px-6 py-4 border-b text-gray-700 dark:text-gray-300">{{ ucfirst($devolucion->tipo_devolucion) }}</td>
                                            <td class="px-6 py-4 border-b text-right font-semibold text-green-600 dark:text-green-400">${{ number_format($devolucion->total_devuelto, 2) }}</td>
                                            <td class="px-6 py-4 border-b text-gray-700 dark:text-gray-300">{{ $devolucion->usuario->name }}</td>
                                            <td class="px-6 py-4 border-b text-gray-700 dark:text-gray-300">{{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 border-b text-center">
                                                <a href="{{ route('cajero.devoluciones.show', $devolucion) }}" class="bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white px-3 py-2 rounded-lg text-sm font-semibold">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $devoluciones->links() }}
                        </div>
                    @else
                        <div class="text-center py-16 bg-gray-50 dark:bg-gray-700 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Aún no hay devoluciones</h3>
                            <p class="text-gray-600 dark:text-gray-400">Procesa una devolución desde el historial de ventas para que aparezca aquí.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
