<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Historial de gestiones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Registro de gestiones de recepciones') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Consulta tus acciones más recientes en recepciones de compras.') }}</p>
                        </div>
                        <a href="{{ route('bodeguero') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            {{ __('Volver al dashboard') }}
                        </a>
                    </div>

                    @if($historiales->isEmpty())
                        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            {{ __('Aún no hay registros de gestiones. Registra una recepción para crear el historial.') }}
                        </div>
                    @else
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Fecha') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Acción') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Compra') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Producto') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Descripción') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($historiales as $gestion)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $gestion->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $gestion->accion)) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">#{{ $gestion->compra->id ?? '-' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $gestion->detalle->producto->nombre ?? '-' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $gestion->descripcion }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $historiales->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
