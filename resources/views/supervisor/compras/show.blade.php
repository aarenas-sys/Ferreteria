<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openCloseModal: false }">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Detalle de compra #{{ $compra->id }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Revisa el estado y los detalles de la compra.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('supervisor.compras.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Volver al listado
                    </a>

@if($compra->isPendienteConfirmacion())
                    <form method="POST" action="{{ route('supervisor.compras.confirmar-recepcion', $compra) }}" onsubmit="return confirm('¿Confirmar recepción de mercancía?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Confirmar recepción
                        </button>
                    </form>
                @elseif(!$compra->isRecibida())
                        <button type="button" @click="openCloseModal = true" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Cerrar compra
                        </button>
                    @endif

                    @if($compra->isPendiente())
                        <form method="POST" action="{{ route('supervisor.compras.destroy', $compra) }}" onsubmit="return confirm('¿Eliminar esta compra pendiente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Eliminar compra
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div x-show="openCloseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6" @keydown.escape.window="openCloseModal = false">
                <div class="w-full max-w-xl rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cerrar compra #{{ $compra->id }}</h3>
                        <button type="button" @click="openCloseModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">✕</button>
                    </div>
                    <form action="{{ route('supervisor.compras.cerrar', $compra) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Registra la observación de cierre antes de marcar la compra como recibida.</p>
                            <div>
                                <label for="observacion_cierre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observación de cierre</label>
                                <textarea id="observacion_cierre" name="observacion_cierre" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">{{ old('observacion_cierre') }}</textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                            <button type="button" @click="openCloseModal = false" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200">Cancelar</button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500">Confirmar cierre</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid gap-6 sm:grid-cols-3 mb-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Proveedor</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->proveedor->nombre }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipo de pago</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($compra->tipo_pago) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</h3>
                            <p class="mt-1">
                                @if($compra->isPendiente())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800">Pendiente</span>
                                @elseif($compra->isParcial())
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-sm font-medium text-yellow-800">Parcial</span>
                                @elseif($compra->isPendienteConfirmacion())
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-sm font-medium text-indigo-800">Pendiente de confirmación</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800">Recibida</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($compra->fecha_cierre || $compra->observacion_cierre)
                        <div class="grid gap-6 sm:grid-cols-2 mb-6">
                            @if($compra->fecha_cierre)
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fecha de cierre</h3>
                                    <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->fecha_cierre->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif
                            @if($compra->observacion_cierre)
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Observación de cierre</h3>
                                    <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->observacion_cierre }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="grid gap-6 sm:grid-cols-3 mb-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total estimado</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ number_format($compra->total_estimado, 2) }}</p>
                        </div>                        @if($compra->isRecibida() && $compra->fecha_recepcion)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fecha de recepción</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->fecha_recepcion->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total real</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->total_real !== null ? number_format($compra->total_real, 2) : '0.00' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Creada por</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->usuario->name }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad solicitada</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad recibida</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio compra</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($compra->detalles as $detalle)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->producto->nombre }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->cantidad_solicitada }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->cantidad_recibida }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ number_format($detalle->precio_compra, 2) }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
