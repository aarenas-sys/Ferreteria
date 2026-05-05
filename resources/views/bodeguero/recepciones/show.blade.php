<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Recepción de compra #{{ $compra->id }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Registra las cantidades recibidas para cada producto.</p>
            </div>
            <a href="{{ route('bodeguero.recepciones.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="grid gap-6 sm:grid-cols-3 mb-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Proveedor</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $compra->proveedor->nombre }}</p>
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
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total estimado</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ number_format($compra->total_estimado, 2) }}</p>
                        </div>
                    </div>

                    <form action="{{ route('bodeguero.recepciones.update', $compra) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibido</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrar</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($compra->detalles as $detalle)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->producto->nombre }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->cantidad_solicitada }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->cantidad_recibida }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                                <input type="hidden" name="detalles[{{ $detalle->id }}][id]" value="{{ $detalle->id }}">
                                                <input type="number" name="detalles[{{ $detalle->id }}][cantidad_recibida]" min="0" value="{{ old('detalles.' . $detalle->id . '.cantidad_recibida', $detalle->cantidad_recibida) }}" {{ $detalle->cantidad_recibida > $detalle->cantidad_solicitada ? 'readonly' : '' }} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100 {{ $detalle->cantidad_recibida > $detalle->cantidad_solicitada ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : '' }}">
                                                @if($detalle->cantidad_recibida > $detalle->cantidad_solicitada)
                                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Cantidad excedente registrada - no editable</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $detalle->cantidad_solicitada - $detalle->cantidad_recibida }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex items-center justify-between gap-4">
                            <a href="{{ route('bodeguero.recepciones.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Guardar recepción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
