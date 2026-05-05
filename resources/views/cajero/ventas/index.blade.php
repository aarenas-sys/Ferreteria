<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lista de Ventas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('error'))
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-400 px-6 py-4 rounded-lg mb-6">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Historial de Ventas</h3>
                        <a href="{{ route('cajero.ventas.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Nueva Venta
                        </a>
                    </div>

            @if($ventas->count() > 0)
                    <div class="space-y-4">
                        @forelse($ventas as $venta)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden transition-colors duration-200">
                            <!-- Encabezado de la venta -->
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 transition-colors duration-200">
                                <div class="flex-1">
                                    <div class="grid grid-cols-7 gap-4 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">#{{ $venta->id }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Fecha</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cliente</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $venta->cliente ? $venta->cliente->nombre_completo : 'Anónimo' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Tipo</p>
                                            <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full {{ $venta->tipo_venta === 'contado' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400' }}">
                                                {{ ucfirst($venta->tipo_venta) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                            <p class="font-bold text-green-600 dark:text-green-400">${{ number_format($venta->total, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Estado</p>
                                            <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full
                                                {{ $venta->estado === 'completada' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : ($venta->estado === 'pendiente_pago' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400') }}">
                                                {{ ucfirst(str_replace('_', ' ', $venta->estado)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cajero</p>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $venta->usuario->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="px-6 py-3 bg-white dark:bg-gray-800 flex gap-2">
                                <a href="{{ route('cajero.ventas.show', $venta) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Ver Detalle
                                </a>
                                <a href="{{ route('cajero.ventas.factura', $venta) }}" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Factura
                                </a>
                                @if($venta->tipo_venta === 'contado' && now()->diffInDays($venta->fecha_venta) <= 15)
                                    <a href="{{ route('cajero.ventas.devolucion.create', $venta) }}" class="inline-flex items-center px-3 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Devolución
                                    </a>
                                @endif
                                @if($venta->devoluciones_count > 0)
                                    <span class="px-3 py-2 rounded-lg bg-indigo-500 text-white text-xs font-semibold">Devolución registrada</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        @endforelse
                    </div>

                <div class="mt-6">
                    {{ $ventas->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 transition-colors duration-200">
                    <svg class="w-20 h-20 mx-auto text-gray-400 dark:text-gray-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-3">No hay ventas registradas</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg">Comienza creando tu primera venta.</p>
                    <a href="{{ route('cajero.ventas.create') }}" class="inline-flex items-center px-8 py-4 bg-blue-600 border border-transparent rounded-md font-bold text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Crear Primera Venta
                    </a>
                </div>
            @endif
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="{{ route('cajero') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    ← Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>