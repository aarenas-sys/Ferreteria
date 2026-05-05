<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Descuentos por Sucursal
            </h2>
            <a href="{{ route('admin.discounts.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Nuevo Descuento
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-6">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Nombre</th>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Valor</th>
                                <th class="px-4 py-2 text-left">Estado</th>
                                <th class="px-4 py-2 text-left">Rango</th>
                                <th class="px-4 py-2 text-left">Sucursales</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($discounts as $discount)
                                <tr>
                                    <td class="px-4 py-3">{{ $discount->name }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($discount->type) }}</td>
                                    <td class="px-4 py-3">{{ $discount->value }}{{ $discount->type === 'percentage' ? '%' : '' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $discount->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $discount->active ? 'Activo' : 'Desactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $discount->fecha_inicio?->format('d/m/Y') ?? '–' }}
                                        -
                                        {{ $discount->fecha_fin?->format('d/m/Y') ?? '–' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $discount->branches->pluck('name')->join(', ') }}
                                    </td>
                                    <td class="px-4 py-3 space-x-2">
                                        <a href="{{ route('admin.discounts.edit', $discount) }}" class="text-blue-500 hover:text-blue-700">Editar</a>

                                        <form action="{{ route('admin.discounts.destroy', $discount) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar este descuento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $discounts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
