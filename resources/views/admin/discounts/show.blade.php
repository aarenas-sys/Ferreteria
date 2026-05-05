<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detalle de Descuento
            </h2>
            <a href="{{ route('admin.discounts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <strong>Nombre:</strong> {{ $discount->name }}
                        </div>
                        <div>
                            <strong>Tipo:</strong> {{ ucfirst($discount->type) }}
                        </div>
                        <div>
                            <strong>Valor:</strong> {{ $discount->value }}{{ $discount->type === 'percentage' ? '%' : '' }}
                        </div>
                        <div>
                            <strong>Activo:</strong> {{ $discount->active ? 'Sí' : 'No' }}
                        </div>
                        <div>
                            <strong>Fecha inicio:</strong> {{ $discount->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        <div>
                            <strong>Fecha fin:</strong> {{ $discount->fecha_fin?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        <div class="md:col-span-2">
                            <strong>Sucursales:</strong> {{ $discount->branches->pluck('name')->join(', ') ?: 'Ninguna' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
