<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detalle del proveedor
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Revisa la información completa de este proveedor.</p>
            </div>
            <a href="{{ route('supervisor.proveedores.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nombre</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $proveedor->nombre }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">NIT</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $proveedor->nit }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Teléfono</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $proveedor->telefono }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">{{ $proveedor->email }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">
                                @if($proveedor->activo)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-medium text-green-800">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-medium text-red-800">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dirección</h3>
                        <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $proveedor->direccion ?? 'Sin dirección registrada' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
