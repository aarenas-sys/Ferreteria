<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Panel de Control de Bodeguero') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("¡Bienvenido al Panel de Bodeguero!") }}
                    <p>Gestiona el inventario, registra recepciones y consulta tu historial de gestiones.</p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('bodeguero.recepciones.index') }}" class="block p-6 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Recepciones de Compra</h3>
                            <p>Registra y gestiona las recepciones de productos de proveedores.</p>
                        </a>

                        <a href="{{ route('bodeguero.historial.index') }}" class="block p-6 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg shadow-md transition-colors duration-200">
                            <h3 class="text-lg font-semibold">Historial de Gestiones</h3>
                            <p>Consulta el historial completo de tus gestiones en el sistema.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>