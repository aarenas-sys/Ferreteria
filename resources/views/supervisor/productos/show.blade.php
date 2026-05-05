<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalle del producto
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Revisa la información completa de este producto.</p>
            </div>
            <a href="{{ route('supervisor.productos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Código</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $producto->codigo }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nombre</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $producto->nombre }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Precio</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ number_format($producto->precio, 2) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $producto->stock }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock mínimo</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $producto->stock_minimo }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sucursal</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $producto->sucursal->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Descripción</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $producto->descripcion ?? 'Sin descripción' }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Imagen</h3>
                        @if($producto->imagen)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="Imagen del producto" class="max-w-xs h-auto rounded-lg shadow">
                            </div>
                        @else
                            <p class="mt-2 text-gray-500 dark:text-gray-400">Sin imagen</p>
                        @endif
                    </div>

                    @if($producto->isLowStock())
                        <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
                            El stock está por debajo del mínimo definido. Actualiza el inventario cuanto antes.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
